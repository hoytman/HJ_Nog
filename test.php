<?php 
require_once("Nog.php");

Nog::init();

Nog::O();

Class myClass{

    public function __construct() {
        Nog::O();
        $this->one(10,10);
        Nog::C();
    }
    
    function one($a, $b){
        Nog::O();
        $a += 2;
        $b -= 3;
        Nog::M("a increased by 2");
        Nog::M("b decreased by 3");
        $c = $this->two($a,$b);
        Nog::C();
        return $c;
    }

    function two($a, $b){
        Nog::O();
        $a += 1;
        $b -= 2;
        Nog::M("a increased by 1");
        Nog::M("b decreased by 2");
        $c = $this->three($a,$b);
        Nog::C();
        return $c;
    }

    function three($a, $b){
        Nog::O();
        $c = $a + $b;
        Nog::M("a + b = ", $c);
        Nog::C();
        return $c;
    }

}

function call_these_classes(){
    Nog::O();
    $x = new myClass();
    $y = new myClass();
    Nog::C();
}

call_these_classes();

call_these_classes();

Nog::C();

Echo "Done: ".time();