<?php


class V extends CI_Controller{

    public function verify(){
        // Retired because document numbers are not bearer secrets and allowed
        // public record enumeration. Use the signed Pages/verify URL instead.
        show_error('This legacy verification endpoint has been disabled.', 410);

}

}
