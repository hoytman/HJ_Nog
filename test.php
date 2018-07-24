<?php 
require_once("Nog.php");

Nog::init("nog","test");

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
        Nog::A('function 2');
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

function recursive($x){
    Nog::O();
    
    Nog::M("get ready to sleep");
    
    $sleep = rand(1,1000);
    usleep($sleep);
    Nog::M("sleep:".$sleep);
    

    $x--;
    Nog::M("x=", $x);
    if($x%10 == 0){
       Nog::A("recursive $x"); 
    }
    if($x > 0){
        recursive($x);
    }
    Nog::C();
}

function random_test(){
    Nog::O();
    $x = rand(0,100);
    Nog::M("x=", $x);
    if($x > 50){
        random_test();
    }
    $x = rand(0,100);
    Nog::M("x=", $x);
    if($x > 50){
        random_test();
    }

    Nog::C();
}

function call_these_classes(){
    Nog::O();
    $x = new myClass();
    $y = new myClass();
    Nog::C();
}

call_these_classes();
Nog::A('middle');
call_these_classes();
Nog::A('Recursion');

recursive(100);

Nog::A('Random');

random_test();

Nog::A('Bottom');

Nog::C();

Echo "Done: ".time();

Nog::O();
Nog::C();