<div class="header-user">
    <?
    if($USER->IsAuthorized())
    {           
        $budget = floatval(CUserEx::getBudget());
        $arUser = CUserEx::updateAvatar($USER->GetID());
        
        if(floatval($arUser["UF_CAPACITY_BUSY"])==0 || floatval($arUser["UF_CAPACITY"])==0)
        {
            $filledPercent = 0;
        }else{
            $filledPercent = round(floatval($arUser["UF_CAPACITY_BUSY"])/floatval($arUser["UF_CAPACITY"]), 4);
        }
        
        $APPLICATION->AddViewContent('user_budget', number_format($budget, 0, "", " "));
        $APPLICATION->AddViewContent('user_filled_space', round($arUser["UF_CAPACITY_BUSY"], 2));
        $APPLICATION->AddViewContent('user_filled_space_percent', $filledPercent);  
        ?>
        
        <div class="fill-disk-space" data-type="fill-disk-space">
    		<div class="progress-holder" data-progress="<?=$APPLICATION->ShowViewContent('user_filled_space_percent');?>"></div>
    		<span class="label">Занято <strong><?=$APPLICATION->ShowViewContent('user_filled_space');?> ГБ</strong></span>
    	</div>                

        <nav class="header-nav" data-module="user-navigation">
            <div class="user-card">
				<a href="/personal/" class="user-avatar<?if(!$arUser["PERSONAL_PHOTO"]):?> is-empty<?endif;?>" data-type="avatar-holder">
                    <?if($arUser["PERSONAL_PHOTO"]):?>
                        <img src="<?=CFile::GetPath($arUser["PERSONAL_PHOTO"])?>" alt="<?=$USER->GetFullName()?>" width="50" height="50">
                    <?endif;?>
                </a>
                <?
                if(strpos($APPLICATION->GetCurDir(), "personal")===false)
                {
                    $urlExit = $APPLICATION->GetCurPageParam("logout=yes", array("logout"));
                }else{
                    $urlExit = "/?logout=yes";
                }
                ?>
				<div class="info-panel">
					<a class="username" href="/personal/"><?=$USER->GetFullName()?></a>
					<a href="<?=$urlExit?>" class="signout-link">Выйти</a>
				</div>
			</div>
        </nav>
        <?
    }else{
        ?>
        <nav class="header-nav" data-module="user-navigation">
            <ul class="user-actions">
                <li><a href="#" class="signin-link" data-type="signin-overlay-toggle">Войти</a></li>
    			<li><a href="#" class="signup-link" data-type="signup-overlay-toggle">Зарегистрироваться</a></li>
            </ul>
        </nav>
        <?
    }
    ?>
    
    <?if(!$USER->IsAuthorized()):?>
        <?$APPLICATION->IncludeComponent("bitrix:system.auth.form", "auth_ajax",Array(
             "REGISTER_URL" => "register.php",
             "FORGOT_PASSWORD_URL" => "",
             "PROFILE_URL" => "/",
             "SHOW_ERRORS" => "Y" 
             )
        );?>
        <?$APPLICATION->IncludeComponent("bitrix:system.auth.registration","",Array());?>
        <?$APPLICATION->IncludeComponent(
            "bitrix:system.auth.forgotpasswd",
            ".default",
            Array()
        );?>
        <div class="authorize-overlay is-success-signup-overlay" data-module="success-signup-overlay">
    		<div class="overlay-content">
    			<h4 class="overlay-title">Поздравляем вас</h4>
    			<p>Вы успешно зарегистрировались на МЕГАТВ.</p>
    			<a href="/" class="btn btn-primary btn-block">Начать пользоваться сервисом</a>
    		</div>
    	</div>
    	<div class="authorize-overlay is-success-reset-overlay" data-module="success-reset-overlay">
    		<div class="overlay-content">
    			<h4 class="overlay-title">Пароль изменён</h4>
    			<p>Вы успешно изменили пароль для входа в свой аккаунт на МЕГАТВ.</p>
    			<a href="#" class="btn btn-primary btn-block" data-type="signin-handler-link">Авторизоваться</a>
    		</div>
    	</div>
    <?endif;?>
</div>