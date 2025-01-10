<?php

/**
 * @package modules\images
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Images\UserApi;

use Xaraya\Modules\Images\Image_GD;
use Xaraya\Modules\MethodClass;
use xarMod;
use xarModVars;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * images userapi load_image function
 */
class LoadImageMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Load an image object for further manipulation
     * @param int $fileId The (uploads) file id of the image to load, or
     * @param string $fileLocation The file location of the image to load
     * @param string $thumbsdir (optional) The directory where derivative images are stored
     * @return object|null Image_GD (or other) object
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (empty($fileId) && empty($fileLocation)) {
            $mesg = xarML(
                "Invalid parameter '#(1)' to API function '#(2)' in module '#(3)'",
                '',
                'load_image',
                'images'
            );
            throw new BadParameterException(null, $mesg);
        } elseif (!empty($fileId) && !is_string($fileId)) {
            $mesg = xarML(
                "Invalid parameter '#(1)' to API function '#(2)' in module '#(3)'",
                'fileId',
                'load_image',
                'images'
            );
            throw new BadParameterException(null, $mesg);
        } elseif (!empty($fileLocation) && !is_string($fileLocation)) {
            $mesg = xarML(
                "Invalid parameter '#(1)' to API function '#(2)' in module '#(3)'",
                'fileLocation',
                'load_image',
                'images'
            );
            throw new BadParameterException(null, $mesg);
        }

        // if both arguments are specified, give priority to fileId
        if (!empty($fileId) && is_numeric($fileId)) {
            // if we only get the fileId
            if (empty($fileLocation) || !isset($storeType)) {
                $fileInfoArray = xarMod::apiFunc('uploads', 'user', 'db_get_file', ['fileId' => $fileId]);
                $fileInfo = end($fileInfoArray);
                if (empty($fileInfo)) {
                    return null;
                }
                if (!empty($fileInfo['fileLocation']) && file_exists($fileInfo['fileLocation'])) {
                    // pass the file location to Image_Properties
                    $location = $fileInfo['fileLocation'];
                } elseif (defined('_UPLOADS_STORE_DB_DATA') && ($fileInfo['storeType'] & _UPLOADS_STORE_DB_DATA)) {
                    // pass the file info array to Image_Properties
                    $location = $fileInfo;
                }

                // if we get the whole file info
            } elseif (file_exists($fileLocation)) {
                $location = $fileLocation;
            } elseif (defined('_UPLOADS_STORE_DB_DATA') && ($storeType & _UPLOADS_STORE_DB_DATA)) {
                // pass the whole array to Image_Properties
                $location = $args;
            } else {
                $mesg = xarML(
                    "Invalid parameter '#(1)' to API function '#(2)' in module '#(3)'",
                    'fileLocation',
                    'load_image',
                    'images'
                );
                throw new BadParameterException(null, $mesg);
            }
        } else {
            $location = $fileLocation;
        }

        if (empty($thumbsdir)) {
            $thumbsdir = xarModVars::get('images', 'path.derivative-store');
        }

        sys::import('modules.images.class.image_properties');

        switch (xarModVars::get('images', 'type.graphics-library')) {
            /**
            case _IMAGES_LIBRARY_IMAGEMAGICK:
                sys::import('modules.images.class.image_ImageMagick');
                $newImage = new Image_ImageMagick($location, $thumbsdir);
                return $newImage;
                break;
            case _IMAGES_LIBRARY_NETPBM:
                sys::import('modules.images.class.image_NetPBM');
                $newImage = new Image_NetPBM($location, $thumbsdir);
                return $newImage;
                break;
             */
            default:
            case _IMAGES_LIBRARY_GD:
                sys::import('modules.images.class.image_gd');
                $newImage = new Image_GD($location, $thumbsdir);
                return $newImage;
        }
    }
}
