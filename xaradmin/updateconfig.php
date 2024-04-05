<?php
/**
 * Images module - update config
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
 * Update configuration
 * @return bool|void true on success of update
 */
function images_admin_updateconfig(array $args = [], $context = null)
{
    // Get parameters
    if (!xarVar::fetch('libtype', 'list:int:1:3', $libtype, '', xarVar::NOT_REQUIRED)) {
        return;
    }
    if (!xarVar::fetch('file', 'list:str:1:', $file, '', xarVar::NOT_REQUIRED)) {
        return;
    }
    if (!xarVar::fetch('path', 'list:str:1:', $path, '', xarVar::NOT_REQUIRED)) {
        return;
    }
    if (!xarVar::fetch('view', 'list:str:1:', $view, '', xarVar::NOT_REQUIRED)) {
        return;
    }
    if (!xarVar::fetch('shortURLs', 'checkbox', $shortURLs, true)) {
        return;
    }

    if (isset($shortURLs) && $shortURLs) {
        xarModVars::set('images', 'SupportShortURLs', true);
    } else {
        xarModVars::set('images', 'SupportShortURLs', false);
    }

    // Confirm authorisation code.
    if (!xarSec::confirmAuthKey()) {
        return;
    }

    if (isset($libtype) && is_array($libtype)) {
        foreach ($libtype as $varname => $value) {
            // check to make sure that the value passed in is
            // a real images module variable
            if (null !== xarModVars::get('images', 'type.' . $varname)) {
                xarModVars::set('images', 'type.' . $varname, $value);
            }
        }
    }
    if (isset($file) && is_array($file)) {
        foreach ($file as $varname => $value) {
            // check to make sure that the value passed in is
            // a real images module variable
            if (null !== xarModVars::get('images', 'file.' . $varname)) {
                xarModVars::set('images', 'file.' . $varname, $value);
            }
        }
    }
    if (isset($path) && is_array($path)) {
        foreach ($path as $varname => $value) {
            // check to make sure that the value passed in is
            // a real images module variable
            $value = trim(preg_replace('~\/$~', '', $value));
            if (null !== xarModVars::get('images', 'path.' . $varname)) {
                if (!file_exists($value) || !is_dir($value)) {
                    $msg = xarML('Location [#(1)] either does not exist or is not a valid directory!', $value);
                    throw new BadParameterException(null, $msg);
                } elseif (!is_writable($value)) {
                    $msg = xarML('Location [#(1)] can not be written to - please check permissions and try again!', $value);
                    throw new BadParameterException(null, $msg);
                } else {
                    xarModVars::set('images', 'path.' . $varname, $value);
                }
            }
        }
    }
    if (isset($view) && is_array($view)) {
        foreach ($view as $varname => $value) {
            // check to make sure that the value passed in is
            // a real images module variable
            // TODO: add other view.* variables later ?
            if ($varname != 'itemsperpage') {
                continue;
            }
            xarModVars::set('images', 'view.' . $varname, $value);
        }
    }

    if (!xarVar::fetch('basedirs', 'isset', $basedirs, '', xarVar::NOT_REQUIRED)) {
        return;
    }
    if (!empty($basedirs) && is_array($basedirs)) {
        $newdirs = [];
        $idx = 0;
        foreach ($basedirs as $id => $info) {
            if (empty($info['basedir']) && empty($info['baseurl']) && empty($info['filetypes'])) {
                continue;
            }
            $newdirs[$idx] = ['basedir' => $info['basedir'],
                                   'baseurl' => $info['baseurl'],
                                   'filetypes' => $info['filetypes'],
                                   'recursive' => (!empty($info['recursive']) ? true : false), ];
            $idx++;
        }
        xarModVars::set('images', 'basedirs', serialize($newdirs));
    }

    xarModHooks::call('module', 'updateconfig', 'images', ['module' => 'images']);
    xarController::redirect(xarController::URL('images', 'admin', 'modifyconfig'), null, $context);

    // Return
    return true;
}
