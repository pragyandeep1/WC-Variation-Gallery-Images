<?php

defined('ABSPATH') || exit;
?>
<script type="text/html" id="tmpl-rtwpvg-slider-template">

    <# hasVideo = (  data.rtwpvg_video_link ) ? 'rtwpvg-gallery-video' : '' #>
    <# thumbnailSrc = (  data.rtwpvg_video_link ) ? data.video_thumbnail_src : data.gallery_thumbnail_src #>
    <# videoHeight = ( data.rtwpvg_video_width ) ? data.rtwpvg_video_width : 'auto' #>
    <# videoWidth = ( data.rtwpvg_video_height ) ? data.rtwpvg_video_height : '100%' #>
    <# swiperClass = ( rtwpvg.using_swiper ) ? 'swiper-slide' : '' #>

    <div class="rtwpvg-gallery-image {{swiperClass}} {{hasVideo}} rtwpvg-gallery-image-{{data.image_id}}">
        <# if(data.rtwpvg_video_link ) { #>
        <# if(data.rtwpvg_video_embed_type == 'video') { #>
        <div class="rtwpvg-single-video-container">
            <video disablePictureInPicture preload="auto" controls controlsList="nodownload"
                   src="{{ data.rtwpvg_video_link }}"
                   poster="{{data.src}}"
                   style="width: {{videoWidth}}; height: {{videoHeight}}; margin: 0;padding: 0; background-color: #000"></video>
        </div>
        <# } #>
        <# if(data.rtwpvg_video_embed_type == 'iframe') { #>
        <div class="rtwpvg-single-video-container">
            <iframe class="rtwpvg-lightbox-iframe" src="{{ data.rtwpvg_video_embed_url }}"
                    style="width: {{ videoWidth }}; height: {{videoHeight}}; margin: 0;padding: 0; background-color: #000"
                    frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
        </div>
        <# } #>
        <# }else{ #>
            <# if( data.src ){ #>

                <div class="rtwpvg-single-image-container">
                    <# if( data.srcset ){ #>
                    <img class="{{data.class}}" width="{{data.src_w}}" height="{{data.src_h}}" src="{{data.src}}"
                        alt="{{data.alt}}" title="{{data.title}}" data-caption="{{data.caption}}" data-src="{{data.full_src}}" data-large_image="{{data.full_src}}"
                        data-large_image_width="{{data.full_src_w}}" data-large_image_height="{{data.full_src_h}}"
                        srcset="{{data.srcset}}" sizes="{{data.sizes}}" {{data.extra_params}}/>
                    <# }else{ #>
                    <img class="{{data.class}}" width="{{data.src_w}}" height="{{data.src_h}}" src="{{data.src}}"
                        alt="{{data.alt}}" title="{{data.title}}" data-caption="{{data.caption}}" data-src="{{data.full_src}}" data-large_image="{{data.full_src}}"
                        data-large_image_width="{{data.full_src_w}}" data-large_image_height="{{data.full_src_h}}"
                        sizes="{{data.sizes}}" {{data.extra_params}}/>
                    <# } #>
                </div>

            <# } #>
        <# } #>
    </div>
</script>