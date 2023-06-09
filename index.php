<?php
/*
 *  Jirafeau, your web file repository
 *  Copyright (C) 2013
 *  Jerome Jutteau <jerome@jutteau.fr>
 *  Jimmy Beauvois <jimmy.beauvois@gmail.com>
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
session_start();
define('JIRAFEAU_ROOT', dirname(__FILE__) . '/');

require(JIRAFEAU_ROOT . 'lib/settings.php');
require(JIRAFEAU_ROOT . 'lib/functions.php');
require(JIRAFEAU_ROOT . 'lib/lang.php');

if ($cfg['download_password_requirement'] === "generated"){
    $download_pass = jirafeau_gen_download_pass($cfg['download_password_gen_len'], $cfg['download_password_gen_chars']);
}

check_errors($cfg);
if (has_error()) {
    require(JIRAFEAU_ROOT . 'lib/template/header.php');
    show_errors();
    require(JIRAFEAU_ROOT . 'lib/template/footer.php');
    exit;
}
require(JIRAFEAU_ROOT . 'lib/template/header.php');

// Logout action
if (isset($_POST['action']) && (strcmp($_POST['action'], 'logout') == 0)) {
    jirafeau_session_end();
}

/* Check if user is allowed to upload. */
// First check: Is user already logged
if (jirafeau_user_session_logged()) {
}
// Second check: Challenge by IP NO PASSWORD
elseif (true === jirafeau_challenge_upload_ip_without_password($cfg, get_ip_address($cfg))) {
    jirafeau_user_session_start();
}
// Third check: Challenge by IP
elseif (true === jirafeau_challenge_upload_ip($cfg, get_ip_address($cfg))) {
    // Is an upload password required?
    if (jirafeau_has_upload_password($cfg)) {
        // Challenge by password
        if (isset($_POST['upload_password'])) {
            if (jirafeau_challenge_upload_password($cfg, $_POST['upload_password'])) {
                jirafeau_user_session_start();
            } else {
                jirafeau_session_end();
                jirafeau_fatal_error(t('BAD_PSW'), $cfg);
            }
        }

        // Show login form if user session is not authorized yet
        if (!jirafeau_user_session_logged()) {
            ?>
            <form method="post" class="form login">
            <fieldset>
                <table>
                <tr>
                    <td class = "label"><label for = "enter_password">
                    <?php echo t('UP_PSW') . ':'; ?></label>
                    </td>
                </tr><tr>
                    <td class = "field"><input type = "password"
                    name = "upload_password" id = "upload_password"
                    size = "40" autocomplete = "current-password" />
                    </td>
                </tr>
                <tr class = "nav">
                    <td class = "nav next">
                    <input type = "submit" name = "key" value = "<?php echo t('LOGIN'); ?>" />
                    </td>
                </tr>
                </table>
            </fieldset>
            </form>
            <?php
            require(JIRAFEAU_ROOT.'lib/template/footer.php');
            exit;
        }
    }
} else {
    jirafeau_fatal_error(t('ACCESS_KO'), $cfg);
}

?>
<div id="upload_finished">
    <p><?php echo t('FILE_UP') ?></p>

    <div id="upload_finished_download_page">
    <p>
        <a id="upload_link" href=""><?php echo t('DL_PAGE') ?></a>
        <a id="upload_link_email" href=""><img id="upload_image_email"/></a>
    </p><p>
        <code id=upload_link_text></code>
        <button id="upload_link_button">&#128203;</button>
    </p>
    </div>

    <?php if ($cfg['download_password_requirement'] === "generated"){
    ?>
    <div id="show_password">
    <p><?php echo t('PSW') ?></p>

    <div id="download_password">
    <p>
        <?php echo '<input id="output_key" value="' . $download_pass . '"/>'?>
        <button id="password_copy_button">&#128203;</button>
    </p>
    </div>
    </div>
    <?php
    }?>

    <?php if ($cfg['preview'] == true) {
        ?>
    <div id="upload_finished_preview">
    <p>
        <a id="preview_link" href=""><?php echo t('VIEW_LINK') ?></a>
    </p><p>
        <code id=preview_link_text></code>
        <button id="preview_link_button">&#128203;</button>
    </p>
    </div>
    <?php
    } ?>

    <div id="upload_direct_download">
    <p>
        <a id="direct_link" href=""><?php echo t('DIRECT_DL') ?></a>
    </p><p>
        <code id=direct_link_text></code>
        <button id="direct_link_button">&#128203;</button>
    </p>
    </div>

    <div id="upload_delete">
    <p>
        <a id="delete_link" href=""><?php echo t('DELETE_LINK') ?></a>
    </p><p>
        <code id=delete_link_text></code>
        <button id="delete_link_button">&#128203;</button>
    </p>
    </div>

    <div id="upload_validity">
    <p><?php echo t('VALID_UNTIL'); ?>:</p>
    <p id="date"></p>
    </div>
</div>

<div id="uploading">
    <p>
    <?php echo t('UP'); ?>
    <div id="uploaded_percentage"></div>
    <div id="uploaded_speed"></div>
    <div id="uploaded_time"></div>
    </p>
</div>

<div id="error_pop" class="error">
</div>

<div id="upload">
<form id="upload-form" onsubmit="
            event.preventDefault();
            document.getElementById('upload').style.display = 'none';
            document.getElementById('uploading').style.display = '';
            upload (<?php echo jirafeau_get_max_upload_chunk_size_bytes($cfg['max_upload_chunk_size_bytes']); ?>);
            "><fieldset>
    <legend>
    <?php echo t('SEL_FILE'); ?>
    </legend>
    <p>
        <input type="file" id="file_select" size="30"
    onchange="control_selected_file_size(<?php echo $cfg['maximal_upload_size'] ?>, '<?php
            if ($cfg['maximal_upload_size'] >= 1024) {
                echo t('2_BIG') . ', ' . t('FILE_LIM') . " " . number_format($cfg['maximal_upload_size']/1024, 2) . " GB.";
            } elseif ($cfg['maximal_upload_size'] > 0) {
                echo t('2_BIG') . ', ' . t('FILE_LIM') . " " . $cfg['maximal_upload_size'] . " MB.";
            }
?>')"/>
    </p>

    <div id="options">
        <table id="option_table">
        <?php
        if ($cfg['one_time_download']) {
            echo '<tr><td>' . t('ONE_TIME_DL') . ':</td>';
            echo '<td><input type="checkbox" id="one_time_download" /></td></tr>';
        }
        if ($cfg['download_password_requirement'] === 'generated'){
            echo '<input type="hidden" name="key" id="input_key" value="' . $download_pass .'"/>';
        }else{
            echo '<tr><td><label for="input_key">' . t('PSW') . ':' . '</label></td>';
            echo '<td><input type="password" name="key" id="input_key" autocomplete = "new-password"';
            if ($cfg['download_password_policy'] === 'regex'){
                echo ' pattern="' . substr($cfg['download_password_policy_regex'], 1, strlen($cfg['download_password_policy_regex']) - 2) . '"'; //remove php delimiters
            }
            if ($cfg['download_password_requirement'] === 'required'){
                echo ' required';
            }
            echo '/></td></tr>';
        }?>
        <tr>
        <td><label for="select_time"><?php echo t('TIME_LIM') . ':'; ?></label></td>
        <td><select name="time" id="select_time">
        <?php
$expirationTimeOptions = array(
  array(
    'value' => 'minute',
    'label' => '1_MIN'
  ),
  array(
    'value' => 'hour',
    'label' => '1_H'
  ),
  array(
    'value' => 'day',
    'label' => '1_D'
  ),
  array(
    'value' => 'week',
    'label' => '1_W'
  ),
  array(
      'value' => 'fortnight',
      'label' => '2_W'
  ),
  array(
    'value' => 'month',
    'label' => '1_M'
  ),
  array(
    'value' => 'quarter',
    'label' => '1_Q'
  ),
  array(
    'value' => 'year',
    'label' => '1_Y'
  ),
  array(
    'value' => 'none',
    'label' => 'NONE'
  )
);
foreach ($expirationTimeOptions as $expirationTimeOption) {
    $selected = ($expirationTimeOption['value'] === $cfg['availability_default'])? 'selected="selected"' : '';
    if (true === $cfg['availabilities'][$expirationTimeOption['value']]) {
        echo '<option value="' . $expirationTimeOption['value'] . '" ' .
              $selected . '>' . t($expirationTimeOption['label']) . '</option>';
    }
}
?>
        </select></td>
        </tr>

        <?php
if ($cfg['maximal_upload_size'] >= 1024) {
    echo '<p class="config">' . t('FILE_LIM');
    echo " " . number_format($cfg['maximal_upload_size'] / 1024, 2) . " GB.</p>";
} elseif ($cfg['maximal_upload_size'] > 0) {
    echo '<p class="config">' . t('FILE_LIM');
    echo " " . $cfg['maximal_upload_size'] . " MB.</p>";
} else {
    echo '<p class="config"></p>';
}
?>

        <p id="max_file_size" class="config"></p>
    <p>
    <input type="submit" id="send" value="<?php echo t('SEND'); ?>"/>
    </p>
        </table>
    </div> </fieldset></form>

    <?php
    if (jirafeau_user_session_logged()) {
        ?>
    <form method="post" class="form logout">
        <input type = "hidden" name = "action" value = "logout"/>
        <input type = "submit" value = "<?php echo t('LOGOUT'); ?>" />
    </form>
    <?php
    }
?>

</div>

<script type="text/javascript" lang="Javascript">
// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL-v3-or-Later
    document.getElementById('error_pop').style.display = 'none';
    document.getElementById('uploading').style.display = 'none';
    document.getElementById('upload_finished').style.display = 'none';
    document.getElementById('options').style.display = 'none';
    document.getElementById('send').style.display = 'none';
    if (!check_html5_file_api ())
        document.getElementById('max_file_size').innerHTML = '<?php
        $max_size = jirafeau_get_max_upload_size();
if ($max_size > 0) {
    echo t('NO_BROWSER_SUPPORT') . $max_size;
}
?>';

    addCopyListener('upload_link_button', 'upload_link');
    addCopyListener('preview_link_button', 'preview_link');
    addCopyListener('direct_link_button', 'direct_link');
    addCopyListener('delete_link_button', 'delete_link');
    addTextCopyListener('password_copy_button', 'output_key');
// @license-end
</script>
<?php require(JIRAFEAU_ROOT . 'lib/template/footer.php'); ?>
