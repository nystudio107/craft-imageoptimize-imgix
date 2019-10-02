<?php
/**
 * ImageOptimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimizeimgix\imagetransforms;

use nystudio107\imageoptimize\ImageOptimize;
use nystudio107\imageoptimize\imagetransforms\ImageTransform;

use craft\elements\Asset;
use craft\helpers\ArrayHelper;
use craft\helpers\Assets as AssetsHelper;
use craft\helpers\UrlHelper;
use craft\models\AssetTransform;

use Imgix\UrlBuilder;
use Psr\Http\Message\ResponseInterface;

use Craft;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.1.0
 */
class ImgixImageTransform extends ImageTransform
{
    // Constants
    // =========================================================================

    const TRANSFORM_ATTRIBUTES_MAP = [
        'width'   => 'w',
        'height'  => 'h',
        'quality' => 'q',
        'format'  => 'fm',
    ];

    const IMGIX_PURGE_ENDPOINT = 'https://api.imgix.com/v2/image/purger';

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('image-optimize', 'Imgix');
    }

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $domain;

    /**
     * @var string
     */
    public $apiKey;

    /**
     * @var string
     */
    public $securityToken;

    // Public Methods
    // =========================================================================

    /**
     * @param Asset               $asset
     * @param AssetTransform|null $transform
     *
     * @return string|null
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function getTransformUrl(Asset $asset, $transform)
    {
        $url = null;
        $params = [];
        $settings = ImageOptimize::$plugin->getSettings();

        $domain = $this->domain ?? 'demos.imgix.net';
        $securityToken = $this->securityToken;
        if (ImageOptimize::$craft31) {
            $domain = Craft::parseEnv($domain);
            $securityToken = Craft::parseEnv($securityToken);
        }
        $params['domain'] = $domain;
        $builder = new UrlBuilder($domain);
        if ($asset && $builder) {
            $builder->setUseHttps(true);
            if ($transform) {
                // Map the transform properties
                foreach (self::TRANSFORM_ATTRIBUTES_MAP as $key => $value) {
                    if (!empty($transform[$key])) {
                        $params[$value] = $transform[$key];
                    }
                }
                // Remove any 'AUTO' settings
                ArrayHelper::removeValue($params, 'AUTO');
                // Handle the Imgix auto setting for compression/format
                $autoParams = [];
                if (empty($params['q'])) {
                    $autoParams[] = 'compress';
                }
                if (empty($params['fm'])) {
                    $autoParams[] = 'format';
                }
                if (!empty($autoParams)) {
                    $params['auto'] = implode(',', $autoParams);
                }
                // Handle interlaced images
                if (property_exists($transform, 'interlace')) {
                    if (($transform->interlace != 'none')
                        && (!empty($params['fm']))
                        && ($params['fm'] == 'jpg')
                    ) {
                        $params['fm'] = 'pjpg';
                    }
                }
                if ($settings->autoSharpenScaledImages) {
                    // See if the image has been scaled >= 50%
                    $widthScale = $asset->getWidth() / ($transform->width ?? $asset->getWidth());
                    $heightScale = $asset->getHeight() / ($transform->height ?? $asset->getHeight());
                    if (($widthScale >= 2.0) || ($heightScale >= 2.0)) {
                        $params['usm'] = 50.0;
                    }
                }
                // Handle the mode
                switch ($transform->mode) {
                    case 'fit':
                        $params['fit'] = 'clip';
                        break;

                    case 'stretch':
                        $params['fit'] = 'scale';
                        break;

                    default:
                        // Set a sane default
                        if (empty($transform->position)) {
                            $transform->position = 'center-center';
                        }
                        // Fit mode
                        $params['fit'] = 'crop';
                        $cropParams = [];
                        // Handle the focal point
                        $focalPoint = $asset->getFocalPoint();
                        if (!empty($focalPoint)) {
                            $params['fp-x'] = $focalPoint['x'];
                            $params['fp-y'] = $focalPoint['y'];
                            $cropParams[] = 'focalpoint';
                            $params['crop'] = implode(',', $cropParams);
                        } elseif (preg_match('/(top|center|bottom)-(left|center|right)/', $transform->position)) {
                            // Imgix defaults to 'center' if no param is present
                            $filteredCropParams = explode('-', $transform->position);
                            $filteredCropParams = array_diff($filteredCropParams, ['center']);
                            $cropParams[] = $filteredCropParams;
                            // Imgix
                            if (!empty($cropParams) && $transform->position !== 'center-center') {
                                $params['crop'] = implode(',', $cropParams);
                            }
                        }
                        break;
                }
            } else {
                // No transform was passed in; so just auto all the things
                $params['auto'] = 'format,compress';
            }
            // Apply the Security Token, if set
            if (!empty($securityToken)) {
                $builder->setSignKey($securityToken);
            }
            // Finally, create the Imgix URL for this transformed image
            $assetUri = $this->getAssetUri($asset);
            $url = $builder->createURL($assetUri, $params);
            Craft::debug(
                'Imgix transform created for: '.$assetUri.' - Params: '.print_r($params, true).' - URL: '.$url,
                __METHOD__
            );
        }

        return $url;
    }

    /**
     * @param string              $url
     * @param Asset               $asset
     * @param AssetTransform|null $transform
     *
     * @return string
     */
    public function getWebPUrl(string $url, Asset $asset, $transform): string
    {
        $url = preg_replace('/fm=[^&]*/', 'fm=webp', $url);

        return $url;
    }

    /**
     * @param Asset $asset
     *
     * @return null|string
     * @throws \yii\base\InvalidConfigException
     */
    public function getPurgeUrl(Asset $asset)
    {
        $url = null;

        $domain = $this->domain ?? 'demos.imgix.net';
        $apiKey = $this->apiKey;
        $securityToken = $this->securityToken;
        if (ImageOptimize::$craft31) {
            $domain = Craft::parseEnv($domain);
            $apiKey = Craft::parseEnv($apiKey);
            $securityToken = Craft::parseEnv($securityToken);
        }
        $builder = new UrlBuilder($domain);
        if ($asset && $builder) {
            $builder->setUseHttps(true);
            // Create the Imgix URL for purging this image
            $assetUri = $this->getAssetUri($asset);
            $url = $builder->createURL($assetUri, [
                'domain' => $domain,
                'api-key' => $apiKey,
                'security-token' => $securityToken,
            ]);
            // Strip the query string so we just pass in the raw URL
            $url = UrlHelper::stripQueryString($url);
        }

        return $url;
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    public function purgeUrl(string $url): bool
    {
        $result = false;

        $apiKey = $this->apiKey;
        if (ImageOptimize::$craft31) {
            $apiKey = Craft::parseEnv($apiKey);
        }
        // create new guzzle client
        $guzzleClient = Craft::createGuzzleClient(['timeout' => 120, 'connect_timeout' => 120]);
        // Submit the sitemap index to each search engine
        try {
            /** @var ResponseInterface $response */
            $response = $guzzleClient->post(self::IMGIX_PURGE_ENDPOINT, [
                'auth'        => [
                    $apiKey,
                    '',
                ],
                'form_params' => [
                    'url' => $url,
                ],
            ]);
            // See if it succeeded
            if (($response->getStatusCode() >= 200)
                && ($response->getStatusCode() < 400)
            ) {
                $result = true;
            }
            Craft::info(
                'URL purged: '.$url.' - Response code: '.$response->getStatusCode(),
                __METHOD__
            );
        } catch (\Exception $e) {
            Craft::error(
                'Error purging URL: '.$url.' - '.$e->getMessage(),
                __METHOD__
            );
        }

        return $result;
    }

    /**
     * @param Asset $asset
     *
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function getAssetUri(Asset $asset)
    {
        $volume = $asset->getVolume();

        // If this is a local volume, it implies your are using a "Web Folder"
        // source in Imgix. We can then also infer that:
        // - This volume has URLs
        // - The "Base URL" in Imgix is set to your domain root, per the ImageOptimize docs.
        //
        // Therefore, we need to parse the path from the full URL, so that it
        // includes the path of the volume.
        if ($volume instanceof \craft\volumes\Local) {
            $assetUrl = AssetsHelper::generateUrl($volume, $asset);
            $assetUri = parse_url($assetUrl, PHP_URL_PATH);

            return $assetUri;
        }

        return parent::getAssetUri($asset);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('imgix-image-transform/settings/image-transforms/imgix.twig', [
            'imageTransform' => $this,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules = array_merge($rules, [
            [['domain', 'apiKey', 'securityToken'], 'default', 'value' => ''],
            [['domain', 'apiKey', 'securityToken'], 'string'],
        ]);

        return $rules;
    }
}
