<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
            </main>
			<footer class="site-footer">
				<?/*<div class="fullsize-banner adv-styling-01<?if(strpos($_COOKIE['advertizing_hidden_banners'], "footer-adv")!==false):?> hide<?endif;?>" data-type="advertizing" id="footer-adv">
					<div class="banner-content">
                        <?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/footer-banner.php"), false);?>
					</div>
					<a href="#" class="close-link" data-type="hide-banner-link">Скрыть</a>
				</div>*/?>
				<div class="footer-content">
					<span class="footer-logo"></span>
					<p class="copyrights">
                        <?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/footer-copyright.php"), false);?>
                    </p>
				</div>
			</footer>
			<div class="drop-overlay"></div>
		</div><!-- /.site-wrapper -->
        
        <?
        //$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/vendor.js');
        //$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/plugins.js');
        //$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/main.js');
        ?>
        <script src="<?=SITE_TEMPLATE_PATH?>/megatv/dist/js/vendor.js"></script>
		<script src="<?=SITE_TEMPLATE_PATH?>/megatv/dist/js/plugins.js"></script>
        <?if(strpos($APPLICATION->GetCurDir(), "/channels/")!==false && isset($_REQUEST["SCHEDULE_CODE"])):?>
            <script src="<?=SITE_TEMPLATE_PATH?>/megatv/dist/js/broadcast-card.js"></script>
        <?elseif(strpos($APPLICATION->GetCurDir(), "/channels/")!==false && isset($_REQUEST["CHANNEL_CODE"])):?>
            <script src="<?=SITE_TEMPLATE_PATH?>/megatv/dist/js/channel-card.js"></script>
        <?elseif($APPLICATION->GetCurDir() == "/personal/"):?>
            <script src="<?=SITE_TEMPLATE_PATH?>/megatv/dist/js/user-profile.js"></script>
        <?elseif($APPLICATION->GetCurDir() == "/personal/services/"):?>
            <script src="<?=SITE_TEMPLATE_PATH?>/megatv/dist/js/user-services.js"></script>
        <?elseif($APPLICATION->GetCurDir() == "/personal/records/"):?>
            <script src="<?=SITE_TEMPLATE_PATH?>/megatv/dist/js/user-records.js"></script>
        <?else:?> 
            <script src="<?=SITE_TEMPLATE_PATH?>/megatv/dist/js/main.js"></script>
        <?endif;?>
        <script src="<?=SITE_TEMPLATE_PATH?>/project.js"></script>
        
        <?if(intval($_REQUEST["record_id"])>0 && $_REQUEST["play"]=="yes"):?>
            <script>
                Box.Application.broadcast('playbroadcast', {
        			broadcastID: <?=intval($_REQUEST["record_id"])?>,
        			record: true
        		});
            </script>
        <?endif;?>
        
        <!-- Yandex.Metrika counter -->
        <script type="text/javascript">
            (function (d, w, c) {
                (w[c] = w[c] || []).push(function() {
                    try {
                        w.yaCounter36131600 = new Ya.Metrika({
                            id:36131600,
                            clickmap:true,
                            trackLinks:true,
                            accurateTrackBounce:true,
                            webvisor:true
                        });
                    } catch(e) { }
                });
        
                var n = d.getElementsByTagName("script")[0],
                    s = d.createElement("script"),
                    f = function () { n.parentNode.insertBefore(s, n); };
                s.type = "text/javascript";
                s.async = true;
                s.src = "https://mc.yandex.ru/metrika/watch.js";
        
                if (w.opera == "[object Opera]") {
                    d.addEventListener("DOMContentLoaded", f, false);
                } else { f(); }
            })(document, window, "yandex_metrika_callbacks");
        </script>
        <noscript><div><img src="https://mc.yandex.ru/watch/36131600" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
        <!-- /Yandex.Metrika counter -->
        
        <!-- Facebook social -->
        <script>
          window.fbAsyncInit = function() {
            FB.init({
              appId      : '1710346209200604',
              xfbml      : true,
              version    : 'v2.5'
            });
          };
        
          (function(d, s, id){
             var js, fjs = d.getElementsByTagName(s)[0];
             if (d.getElementById(id)) {return;}
             js = d.createElement(s); js.id = id;
             js.src = "//connect.facebook.net/en_US/sdk.js";
             fjs.parentNode.insertBefore(js, fjs);
           }(document, 'script', 'facebook-jssdk'));
        </script>
        <!-- Facebook social -->

	</body>
</html>