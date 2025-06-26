<?php
namespace Mageplaza\BannerSlider\Api;
interface BannerInterface {
    /**
     * Returns greeting message to user
     *
     * @api
     * @return string Greeting message
     */
    public function getBanner();
}