<?php
/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <support@zikula.org>.
 * @link http://www.zikula.org
 * @link http://zikula.org
 * @version Generated by ModuleStudio 0.7.0 (http://modulestudio.de) at Mon Nov 02 22:41:56 CET 2015.
 */

namespace Zikula\RoutesModule;

use Zikula\RoutesModule\Base\RoutesModuleVersion as BaseRoutesModuleVersion;

/**
 * Version information implementation class.
 */
class RoutesModuleVersion extends BaseRoutesModuleVersion
{
    public function getMetaData()
    {
        $meta = parent::getMetaData();
        unset($meta['capabilities'][\HookUtil::SUBSCRIBER_CAPABLE]);
        unset($meta['capabilities'][\HookUtil::PROVIDER_CAPABLE]);
        if (count($meta['capabilities']) == 0) {
            unset($meta['capabilities']);
        }

        return $meta;
    }
}