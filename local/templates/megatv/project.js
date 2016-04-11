$(document).on('ready', function(){
    
    /*$('form#login-form').submit(function(){
        var $this = $(this);
        
        $this.find(".email-container").removeClass("has-error");
        $this.find(".email-container .form-control-message").remove();
        
        $.post($this.attr('action'), $this.serialize(), function(data){
            $('input', $this).removeAttr('disabled');
            if (data.type == 'error') 
            {
                $( '<span class="form-control-message">'+data.message+'</span>' ).insertAfter($this.find(".email-container").addClass("has-error").find("input"));
            } else {
                window.location.href = $this.data("redirect");
            }
        }, 'json');
        return false;
    });

    $('form#register-form').on('submit', function(){
        var $form = $(this);
        var form_backurl = $(this).find('input[name="backurl"]').val();

        $form.find("div").removeClass("has-error");
        $form.find(".form-control-message").remove();
        $form.find(".recovery-success").remove();

        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: $(this).serialize(),
            error: function(request,error) {
                alert('Error! Please try again!');
            },
            dataType: "json",
            success: function(data) {
                if(!data.status)
                {
                    errors = data.errors;
                    $.each(errors, function(i, val) {
                        if(i=="AGREE")
                        {
                            $form.find("input[name='"+i+"']").closest("div").addClass("has-error").find("input")
                        }else{
                            $( '<span class="form-control-message">'+val+'</span>' ).insertAfter($form.find("input[name='"+i+"']").closest("div").addClass("has-error").find("input"));
                        }
                    });
                }else{
                    //window.location.href = form_backurl;
                    $( '<br /><div class="form-group recovery-success">'+data.message+'</div>' ).insertAfter($form.find(".btn.btn-primary"));
                }
            }
        });

        return false;
    });
    */
    
    $('form.asd-prepaid-form').on('submit', function(){
        var $form = $(this);

        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: $(this).serialize(),
            error: function(request,error) {
                alert('Error! Please try again!');
            },
            success: function(data) 
            {
                $("#form-pay-request").html(data);
                $("#form-pay-request form").submit();
            }
        });

        return false;
    });
    
    
    
    /*$('form#change-password-form').on('submit', function(){
        var $form = $(this);
        
        $form.find("div").removeClass("has-error");
        $form.find(".form-control-message").remove();

        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: $(this).serialize(),
            error: function(request,error) {
                alert('Error! Please try again!');
            },
            dataType: "json",
            success: function(data) {
                
                if(!data.status)
                {
                    errors = data.errors;
                    $.each(errors, function(i, val) {
       
                        $( '<span class="form-control-message">'+val+'</span>' ).insertAfter($form.find("input[name='"+i+"']").closest("div").addClass("has-error").find("input"));
                        
                    });
                }else{
                    $( '<div class="form-group recovery-success">'+data.message+'</div>' ).insertBefore($("#change-password-form"));
          
                    $(':input','#change-password-form')
                      .not(':button, :submit, :reset, :hidden')
                      .val('')
                      .removeAttr('checked')
                      .removeAttr('selected');
                    
                    setTimeout(function(){ $(".recovery-success").remove(); }, 2000);
                }
            }
        });

        return false;
    });
    
    $('form.user-profile-form, form.user-passport-form').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        
        $form.find("div").removeClass("has-error");
        $form.find(".form-control-message").remove();

        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: $(this).serialize(),
            error: function(request,error) {
                alert('Error! Please try again!');
            },
            dataType: "json",
            success: function(data) {
                console.log(data);
                if(!data.status)
                {
                    errors = data.errors;
                    $.each(errors, function(i, val) {
       
                        $( '<span class="form-control-message">'+val+'</span>' ).insertAfter($form.find("input[name='"+i+"'], textarea[name='"+i+"']").closest("div").addClass("has-error").find("input, textarea"));
                    });
                }else{
                    $( '<div class="form-group recovery-success">'+data.message+'</div>' ).insertBefore($form);
          
                    setTimeout(function(){ $(".recovery-success").remove(); }, 2000);
                }
            }
        });

        return false;
    });*/
    
    
    
    /*
    $('form#recovery-form').submit(function(){
        var $this = $(this);
        
        $this.find(".email-container").removeClass("has-error");
        $this.find(".email-container .form-control-message").remove();
        
        if($this.find(".recovery-success").length>0)
        {
            $this.find(".recovery-success").remove();
        }
        
        $.post($this.attr('action'), $this.serialize(), function(data){
            $('input', $this).removeAttr('disabled');
            console.log(data);
            if (!data.status)
            {
                $( '<span class="form-control-message">'+data.message+'</span>' ).insertAfter($this.find(".email-container").addClass("has-error").find("input"));
            } else {
                $( '<div class="form-group recovery-success">'+data.message+'</div>' ).insertBefore($this.find(".email-container"));
            }
        }, 'json');
        return false;
    });
    */
    
    $(".modal-nav li a").click(function(e){
        e.preventDefault();
        $(".modal-nav li").removeClass("active");
        $("#auth-screens .tab-content .tab-pane").removeClass("in active");
        str = $(this).attr("href");
        $(this).closest("li").addClass("active");
        $(str).addClass("in active");
    });
    
    /*$("#_id-city-select").change(function(){
        $("#city-select-value").val($(this).val());
        $("#city-select-form").submit();
    });*/
    
    /*$('#comment-form .submit-btn').on('click', function(e){
        e.preventDefault();
        
        var $form = $('#comment-form');
        
        $.ajax({
            type: "POST",
            url: $form.attr('action'),
            data: $form.serialize(),
            error: function(request,error) {
                alert('Error! Please try again!');
            },
            dataType: "json",
            success: function(data) {
                
                if(!data.status)
                {
                    errors = data.errors;
                    console.log(errors);
                }else{
                    
                }
            }
        });

        return false;
    });*/
    
    /*$('a#channels-show-ajax-link').on('click', function (e) {
		e.preventDefault();
        var _this = this;
        
        var 
            furl = $(_this).data('load'),
            //feed = $(_this).data('feed'),
            page = $(_this).data('page');
            type = $(_this).data('ajax-type');
        
        if(!$(_this).hasClass("loading"))
        {
            //show loading
            $(_this).hide().addClass("loading");
            $(_this).next('div').css({display: 'inline-block'});
            
            if(furl.indexOf("?")>-1)
            {
                furl=$(_this).data('load') + "&PAGEN_2="+page + "&PAGEN_1="+page;
            }else{
                furl=$(_this).data('load') + "?PAGEN_2="+page + "&PAGEN_1="+page;
            }
            
            //console.log(furl);
            //console.log(type);
            
            $.ajax({
                url: furl,
                type: "POST",
                data: {
                    AJAX : 'Y',
                    AJAX_TYPE : type
                },
                success: function(response) {
                    var $response = $(response);

                    $(".categories-logos").append($response.find(".categories-logos").html());
                    $(".categories-items .row-wrap").append($response.find(".categories-items .row-wrap").html());

                    $('.categories-items [data-type="broadcast"]').data('status-flag', false).data('play-flag', false);
                    
                    renderIcons();
                    
                    $(_this).show().removeClass("loading");
                    $(_this).next('div').hide();
                    $(_this).data('page', ++page);
                },
                error: function() {
                    alert('Error load materials');
                }
            });
        }

        return false;
    });*/
    
    function icon(name, options) {
    	var optionsz = options || {};
    	var size	= optionsz.size ? 'is-' + optionsz.size : '';
    	var klass	= 'icon ' + name + ' ' + size + '' + (optionsz.class || '');
    
    	var iconz	=	'<svg class="icon__cnt">' +
    					'<use xlink:href="#' + name + '" />' +
    					'</svg>';
    
    	var html =  '<div class="' + klass + '">' +
    					wrapSpinner(iconz, klass) +
    				'</div>';
    
    	return html;
    }
    function wrapSpinner(html, klass) 
    {
    	if (klass.indexOf('spinner') > -1) {
    		return '<div class="icon__spinner">' + html + '</div>';
    	} else {
    		return html;
    	}
    }
    function renderIcons() {
    	var icons = document.querySelectorAll('[data-icon]');
    
    	for (var i = 0; i < icons.length; i++) {
    		var currentIcon = icons[i];
    		var name        = currentIcon.getAttribute('data-icon');
    		var options = {
    			class:  currentIcon.className,
    			size:   currentIcon.getAttribute('data-size')
    		};
    
    		currentIcon.insertAdjacentHTML('beforebegin', icon(name, options));
    		currentIcon.parentNode.removeChild(currentIcon);
    	}
    }
    
    $(".badge").click(function(e){
        e.preventDefault();
        channel_id = $(this).data("channel-id");
        pageModule = $('[data-module="page"]').get(0);
        authentication = Box.Application.getModuleConfig(pageModule, 'authentication');
        
        if (authentication === true) 
        {
            if(channel_id!==undefined)
            {
                var modalHTML = $('<div class="modal player-modal fade" id="player-modal"><div class="modal-dialog"><div class="modal-content"></div></div></div>');
        		if ($('#player-modal').length === 0) {
        			$('body').prepend(modalHTML);
        		}
    
        		$.ajax({
        			type: 'GET',
        			dataType: 'html',
        			url: "/local/templates/megatv/ajax/modals/player.php",
        			data: {
        				broadcastID: 0,
        				record: false,
                        channel_id: channel_id
        			},
        			success: function (data) {
        				playerModalContainer = $('.player-modal');
        				playerModalContainer.find('.modal-content').html(data);
        				renderIcons();
        				Box.Application.startAll(playerModalContainer.get(0));
        				modal = Box.Application.getService('modal').create(playerModalContainer, {
        					backdropClass: 'modal-backdrop player-backdrop'
        				});
        				modal.show();
        			}
        		});
            }
        }else{
            $("#mod-signin-overlay-1").addClass("is-visible");
            return;
        }
    });
    
});