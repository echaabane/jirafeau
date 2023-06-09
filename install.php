<?php
/*
 *  Jirafeau, your web file repository
 *  Copyright (C) 2008  Julien "axolotl" BERNARD <axolotl@magieeternelle.org>
 *  Copyright (C) 2015  Nicola Spanti (RyDroid) <dev@nicola-spanti.info>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
define('JIRAFEAU_ROOT', dirname(__FILE__) . '/');

define('JIRAFEAU_CFG', JIRAFEAU_ROOT . 'lib/config.local.php');
define('JIRAFEAU_VAR_RAND_LENGTH', 15);

require(JIRAFEAU_ROOT . 'lib/settings.php');
require(JIRAFEAU_ROOT . 'lib/functions.php');
require(JIRAFEAU_ROOT . 'lib/lang.php');

/**
 * Check installation
 **/

// Is the installation process done already?
// Then there is nothing to do here → redirect to the main page.
if ($cfg['installation_done'] === true) {
    header('Location: index.php');
    exit;
}

/**
 * Prepare installation process
 **/

require(JIRAFEAU_ROOT . 'lib/template/header.php');

// does the local configuration file exist?
if (!file_exists(JIRAFEAU_CFG)) {
    // show an error if it is not possible to create the file
    if (!@touch(JIRAFEAU_CFG)) {
        jirafeau_fatal_error(t('CONF_SOLUTION'));
    }
}

// is the local configuration writable?
if (!is_writable(JIRAFEAU_CFG) && !@chmod(JIRAFEAU_CFG, '0666')) {
    jirafeau_fatal_error(t('CONF_SOLUTION_2'));
}

/**
 * Run trough each installation step
 **/

if (isset($_POST['step']) && isset($_POST['next'])) {
    switch ($_POST['step']) {
        case 1:
            if (strlen($_POST['admin_password'])) {
                $cfg['admin_password'] = hash('sha256', $_POST['admin_password']);
            } else {
                $cfg['admin_password'] = '';
            }
            jirafeau_export_cfg($cfg);
            break;

        case 2:
            $cfg['web_root'] = jirafeau_add_ending_slash($_POST['web_root']);
            $cfg['var_root'] = jirafeau_add_ending_slash($_POST['var_root']);
            jirafeau_export_cfg($cfg);
            break;

        case 3:
            $cfg['web_root'] = jirafeau_add_ending_slash($_POST['web_root']);
            $cfg['var_root'] = jirafeau_add_ending_slash($_POST['var_root']);
            jirafeau_export_cfg($cfg);
            break;
    }
}

$current = 1;
if (isset($_POST['next'])) {
    $current = $_POST['step'] + 1;
} elseif (isset($_POST['previous'])) {
    $current = $_POST['step'] - 1;
} elseif (isset($_POST['retry'])) {
    $current = $_POST['step'];
}

switch ($current) {
    case 1:
    default:
        ?><h2><?php printf(t('JI_INSTALL') . ' - ' . t('STEP') .
        ' %d ' . t('OUT_OF') . ' %d', 1, 3);
        ?></h2> <div id = "install"> <form method="post"> <input type =
        "hidden" name = "jirafeau" value =
        "<?php echo JIRAFEAU_VERSION; ?>" /><input type = "hidden" name =
        "step" value = "1" /><fieldset> <legend><?php
            echo t('ADMIN_PSW');
        ?></legend> <table> <tr> <td class = "info" colspan =
        "2"><?php echo t('ADMIN_INTERFACE_INFO');
        ?></td> </tr> <tr> <td class = "label"><label for = "select_password"
       ><?php echo t('ADMIN_PSW') . ':';
        ?></label></td>
        <td class = "field"><input type = "password" name = "admin_password"
        id = "admin_password" size = "40" autocomplete = "new-password"/></td>
        </tr>
        <tr class = "nav">
        <td></td>
        <td class = "nav next">
        <input type = "submit"
        class = "navleft" name = "previous" value = "<?php
            echo t('PREV_STEP'); ?>" />
        <input type = "submit" name = "next" value =
        "<?php echo t('NEXT_STEP'); ?>" /></td> </tr> </table>
        </fieldset> </form> </div> <?php
break;

    case 2:
        ?><h2><?php printf(t('JI_INSTALL') . ' - ' . t('STEP') .
        ' %d ' . t('OUT_OF') . ' %d', 2, 3);
        ?></h2> <div id = "install"> <form method="post"> <input type =
        "hidden" name = "jirafeau" value =
        "<?php echo JIRAFEAU_VERSION; ?>" /><input type = "hidden" name =
        "step" value =
        "2" /><fieldset> <legend><?php echo t('INFO');
        ?></legend> <table> <tr> <td class = "info" colspan =
        "2"><?php echo t('BASE_ADDR_INFO');
        ?></td> </tr> <tr> <td class = "label"><label for = "input_web_root"
       ><?php echo t('BASE_ADDR') . ':';
        ?></label></td>
        <td class = "field"><input type = "text" name = "web_root"
        id = "input_web_root" value = "<?php
               echo(empty($cfg['web_root']) ? jirafeau_default_web_root() : $cfg['web_root']);
        ?>" size = "40" /></td>
        </tr> <tr> <td class = "info" colspan = "2"><?php
          echo t('DATA_DIR_EXPLAINATION');
        ?></td> </tr> <tr> <td class = "label"><label for = "input_var_root"
       ><?php echo t('DATA_DIR') . ':';
        ?></label></td>
        <td class = "field"><input type = "text" name = "var_root"
        id = "input_var_root" value = "<?php
            if (empty($cfg['var_root'])) {
                $alphanum = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
          'abcdefghijklmnopqrstuvwxyz' . '0123456789';
                $len_alphanum = strlen($alphanum);
                $var = 'var-';
                for ($i = 0; $i <JIRAFEAU_VAR_RAND_LENGTH; $i++) {
                    $var .= substr($alphanum, mt_rand(0, $len_alphanum - 1), 1);
                }
                echo JIRAFEAU_ROOT . $var . '/';
            } else {
                echo $cfg['var_root'];
            }
        ?>" size = "40" /></td>
        </tr> <tr> <td colspan = "2"><input type = "submit"
        class = "navleft" name = "previous" value = "<?php
          echo t('PREV_STEP'); ?>" />
         <input type = "submit" class = "navright" name = "next" value = 
        "<?php echo t('NEXT_STEP'); ?>" />
        </td> </tr> </table> </fieldset>
        </form> </div> <?php
break;

    case 3:
        ?><h2><?php printf(t('JI_INSTALL') . ' - ' . t('STEP') .
        ' %d ' . t('OUT_OF') . ' %d', 3, 3);
        ?></h2> <div id = "install"> <form method="post"> <input type =
        "hidden" name = "jirafeau" value =
        "<?php echo JIRAFEAU_VERSION; ?>" /><input type = "hidden" name =
        "step" value =
        "3" /><fieldset> <legend><?php echo t('FINALIZATION');
        ?></legend> <table> <tr> <td class = "info" colspan =
        "2"><?php echo t('SETTING_UP');
        ?></td> </tr> <tr> <td class = "nav previous"><input type =
        "submit" name = "previous" value = " <?php echo t('PREV_STEP');
        ?>" /></td> <td></td> </tr>
        </table> </fieldset> </form> </div>
    <?php
        $err = jirafeau_check_var_dir($cfg['var_root']);
        if ($err['has_error']) {
            echo '<div class="error"><p>'.$err['why'].'<br />'.NL; ?><form method="post"> <input type = "hidden" name = "jirafeau" value =
            "<?php echo JIRAFEAU_VERSION; ?>" /><input type = "hidden" name =
            "step" value = "3" /><input type = "submit" name =
            "retry" value =
            "<?php echo t('RETRY_STEP'); ?>" /></form>
            <?php echo '</p></div>';
        } else {
            $cfg['installation_done'] = true;
            jirafeau_export_cfg($cfg);
            echo '<div class="message"><p>' .
                 t('JI_FONCTIONAL') . ':' .
                 '<br /><a href="./">' .
                 $cfg['web_root'].'</a></p></div>';
        }
        break;
}

require(JIRAFEAU_ROOT . 'lib/template/footer.php');
