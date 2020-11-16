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
 * @return bool true on success of update
 */
function images_admin_updateconfig()
{
    // Get parameters
    if (!xarVarFetch('libtype', 'list:int:1:3', $libtype, '', XARVAR_NOT_REQUIRED)) {
        return;
    }
    if (!xarVarFetch('file', 'list:str:1:', $file, '', XARVAR_NOT_REQUIRED)) {
        return;
    }
    if (!xarVarFetch('path', 'list:str:1:', $path, '', XARVAR_NOT_REQUIRED)) {
        return;
    }
    if (!xarVarFetch('view', 'list:str:1:', $view, '', XARVAR_NOT_REQUIRED)) {
        return;
    }
    if (!xarVarFetch('shortURLs', 'checkbox', $shortURLs, true)) {
        return;
    }

    if (isset($shortURLs) && $shortURLs) {
        xarModSetVar('images', 'SupportShortURLs', true);
    } else {
        xarModSetVar('images', 'SupportShortURLs', false);
    }

    // Confirm authorisation code.
    if (!xarSecConfirmAuthKey()) {
        return;
    }

    if (isset($libtype) && is_array($libtype)) {
        foreach ($libtype as $varname => $value) {
            // check to make sure that the value passed in is
            // a real images module variable
            if (null !== xarModGetVar('images', 'type.'.$varname)) {
                xarModSetVar('images', 'type.' . $varname, $value);
            }
        }
    }
    if (isset($file) && is_array($file)) {
        foreach ($file as $varname => $value) {
            // check to make sure that the value passed in is
            // a real images module variable
            if (null !== xarModGetVar('images', 'file.'.$varname)) {
                xarModSetVar('images', 'file.' . $varname, $value);
            }
        }
    }
    if (isset($path) && is_array($path)) {
        foreach ($path as $varname => $value) {
            // check to make sure that the value passed in is
            // a real images module variable
            $value = trim(preg_replace('~\/$~', '', $value));
            if (null !== xarModGetVar('images', 'path.' . $varname)) {
                if (!file_exists($value) || !is_dir($value)) {
                    $msg = xarML('Location [#(1)] either does not exist or is not a valid directory!', $value);
                    xarErrorSet(XAR_USER_EXCEPTION, 'INVALID_DIRECTORY', new DefaultUserException($msg));
                    return;
                } elseif (!is_writable($value)) {
                    $msg = xarML('Location [#(1)] can not be written to - please check permissions and try again!', $value);
                    xarErrorSet(XAR_USER_EXCEPTION, 'NOT_WRITABLE', new DefaultUserException($msg));
                    return;
                } else {
                    xarModSetVar('images', 'path.' . $varname, $value);
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
            xarModSetVar('images', 'view.' . $varname, $value);
        }
    }

    if (!xarVarFetch('basedirs', 'isset', $basedirs, '', XARVAR_NOT_REQUIRED)) {
        return;
    }
    if (!empty($basedirs) && is_array($basedirs)) {
        $newdirs = array();
        $idx = 0;
        foreach ($basedirs as $id => $info) {
            if (empty($info['basedir']) && empty($info['baseurl']) && empty($info['filetypes'])) {
                continue;
            }
            $newdirs[$idx] = array('basedir' => $info['basedir'],
                                   'baseurl' => $info['baseurl'],
                                   'filetypes' => $info['filetypes'],
                                   'recursive' => (!empty($info['recursive']) ? true : false));
            $idx++;
        }
        xarModSetVar('images', 'basedirs', serialize($newdirs));
    }

    xarModCallHooks('module', 'updateconfig', 'images', array('module' => 'images'));
    xarResponseRedirect(xarModURL('images', 'admin', 'modifyconfig'));

    // Return
    return true;
}
