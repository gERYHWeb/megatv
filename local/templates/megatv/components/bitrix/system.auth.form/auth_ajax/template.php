<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/*if (strlen($_POST['ajax_key']) && $_POST['ajax_key']==md5('ajax_'.LICENSE_KEY) && htmlspecialcharsbx($_POST["TYPE"])=="AUTH" && check_bitrix_sessid()) 
{
   $APPLICATION->RestartBuffer();
   if (!defined('PUBLIC_AJAX_MODE')) 
   {
      define('PUBLIC_AJAX_MODE', true);
   }
   header('Content-type: application/json');
   if ($arResult['ERROR']) 
   {
      echo json_encode(array(
         'type' => 'error',
         'status' => 'error',
         'message' => strip_tags($arResult['ERROR_MESSAGE']['MESSAGE']),
      ));
   } else {
      echo json_encode(array('type' => 'ok', 'status' => 'ok'));
   }
   require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
   die();
}*/

if ($arResult["FORM_TYPE"] != "login") 
{
    if(strpos($GLOBALS["APPLICATION"]->GetCurDir(), "personal")===false)
    {
        $url = $GLOBALS["APPLICATION"]->GetCurPageParam("logout=yes", array("logout"));
    }else{
        $url = "/?logout=yes";
    }
    ?>
    <a href="<?=$arResult["urlToOwnProfile"]?>" class="view_form u_name"><?=$arResult["FULL_NAME"]?></a>
    <span class="separator">|</span>
    <a href="<?=$url?>" class="view_form exit"><?=GetMessage('AUTH_LOGOUT')?></a>
    <? 
} 
else 
{    
    ?>
    <div class="authorize-overlay is-signin-overlay" data-module="signin-overlay">
		<div class="overlay-content">
			<h4 class="overlay-title"><?=GetMessage('AUTH_LOGIN_BUTTON')?></h4>
            
            <?require($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/include/social-auth.php");?>
            
			<span class="divider"><span><?=GetMessage('AUTH_OR')?></span></span>
            
			<form action="<?= $templateFolder ?>/ajax.php" method="POST" target="_top" id="login-form" class="signin-form" data-redirect="<?=$arParams["PROFILE_URL"]?>">
            	<input type="hidden" name="AUTH_FORM" value="Y" />
            	<input type="hidden" name="TYPE" value="AUTH" />
                <input type="hidden" name="ajax_key" value="<?=md5('ajax_'.LICENSE_KEY)?>" />
                <input type="hidden" id="USER_REMEMBER_frm" name="USER_REMEMBER" value="Y"/>
                
                <?=bitrix_sessid_post()?>
                
        		<div class="form-group email-container" autocomplete="off">
        			<label for="" class="sr-only"><?=GetMessage('AUTH_PHONE_OR_EMAIL')?></label>
        			<input type="text" name="USER_LOGIN" class="form-control" value="<?=$arResult["USER_EMAIL"]?>" placeholder="<?=GetMessage('AUTH_PHONE_OR_EMAIL')?>" autocomplete="off" data-type="adaptive-field" />
        		</div>
                
				<div class="form-group has-feedback" data-type="password-field-group">
					<label for="" class="sr-only"><?=GetMessage('AUTH_PASSWORD')?></label>
                    <input type="password" name="USER_PASSWORD" class="form-control" placeholder="<?=GetMessage('AUTH_PASSWORD')?>" data-type="password-field" autocomplete="off">
					<input type="text" class="form-control" data-type="password-visualizer" placeholder="<?=GetMessage('AUTH_PASSWORD')?>">
					<span class="form-control-feedback">
						<a href="#" data-type="password-visibility-toggle"><span data-icon="icon-password-eye"></span></a>
					</span>
				</div>
                
				<div class="form-actions">
					<button type="submit" name="Login" class="btn btn-primary btn-block btn-multistate" data-type="multistate-button">
						<span class="default-state init-state"><?=GetMessage('AUTH_LOGIN_BUTTON')?></span>
						<span class="done-state"><?=GetMessage('AUTH_AUTHORIZING')?></span>
						<span class="fail-data-state"><span data-icon="icon-msbutton-cross-circle"></span><?=GetMessage('AUTH_CHECK_ENTER_DATA')?></span>
						<span class="fail-network-state"><span data-icon="icon-msbutton-broken-network"></span><?=GetMessage('AUTH_ERROR_SERVER_CONNECT')?></span>
					</button>
					<a href="#" class="form-subaction-link" data-type="reset-handler-link"><?=GetMessage('AUTH_RECOVERY_PASSWORD')?></a>
				</div>
			</form>
		</div>
	</div>
    <?
}
?>