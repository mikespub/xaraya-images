<?php
/**
 * Count the number of uploaded images
 *
 * @package modules
 * @copyright (C) 2002-2007 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Images Module
 * @link http://xaraya.com/index.php/release/152.html
 * @author Images Module Development Team
 */
/**
 * count the number of uploaded images (managed by the uploads module)
 *
 * @author mikespub
 * @return integer the number of uploaded images
 */
function images_adminapi_countuploads(array $args = [], $context = null)
{
    extract($args);
    if (empty($typeName)) {
        $typeName = 'image';
    }

    // Get all uploaded files of mimetype 'image' (cfr. uploads admin view)
    $typeinfo = xarMod::apiFunc('mime', 'user', 'get_type', ['typeName' => $typeName]);
    if (empty($typeinfo)) {
        return;
    }

    $filters = [];
    $filters['mimetype'] = $typeinfo['typeId'];
    $filters['subtype']  = null;
    $filters['status']   = null;
    $filters['inverse']  = null;

    $options  = xarMod::apiFunc('uploads', 'user', 'process_filters', $filters);
    $filter   = $options['filter'];

    $numimages = xarMod::apiFunc('uploads', 'user', 'db_count', $filter);

    return $numimages;
}
