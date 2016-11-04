<?php

class BGImage
{
    private $tradeimage = array(
        'trades'=>'FSR_V2_Trades_BG.jpg',
        '' => 'bg.jpg'
       );
    public function get_image($trade){
        if(isset($this->tradeimage[$trade])){
            return $this->tradeimage[$trade];
        }
    }
}
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>