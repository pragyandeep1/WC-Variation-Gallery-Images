<?php

defined('ABSPATH') || exit;

?>
<script type="text/html" id="tmpl-rtwpvg-thumbnail-template">
    <# hasVideo = (  data.rtwpvg_video_link ) ? 'rtwpvg-thumbnail-video' : '' #>
    <# if( data.gallery_thumbnail_src ) { #>
    <# swiperClass = ( rtwpvg.using_swiper ) ? 'swiper-slide' : '' #>

    <div class="rtwpvg-thumbnail-image {{swiperClass}} {{hasVideo}}">
        <div>
            <img width="{{data.gallery_thumbnail_src_w}}" height="{{data.gallery_thumbnail_src_h}}" src="{{data.gallery_thumbnail_src}}" alt="{{data.alt}}" title="{{data.title}}"/>
        </div>
    </div>
    <# } #>
</script>