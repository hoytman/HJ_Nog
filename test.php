<?php 

require_once("Nog.php");

//This initilizes the function

Nog::init("nog","test");

//This class is created to fhow some nested function calls

Class myClass{

    public function __construct() {
        Nog::O();
        $this->one();
        Nog::C();
    }
    
    function one(){
        Nog::O();
        Nog::M("Lets call two()");
        $this->two();
        Nog::M("Lets call three() ", 2, " times");
        $this->three();
        $this->three();
        Nog::C();
        return $c;
    }

    function two(){
        Nog::O();
        Nog::M("Lets call three()");
        $this->three();
        Nog::C();
    }

    function three(){
        Nog::O();
        $a = rand(1,100);
        $b = rand(1,100);
        $c = $a + $b;
        Nog::M($a, '+', $b, '=',$c);
        Nog::C();
    }

}

// Lets create a few of these classes

function create_some_classes(){
    Nog::O();
    $x = new myClass();
    Nog::A('Create a second Class');
    $y = new myClass();
    Nog::C();
}

//Here are a few more classes to make things interesting

function rest(){
    Nog::O();
    Nog::M("Getting ready to sleep");
    $sleep = rand(1,50);
    usleep($sleep);
    Nog::M("sleep:".$sleep);
    Nog::C();
}

function recursive($x){
    Nog::O();
    rest();
    $x--;
   
    for($i = 0; $i<$x; $i++ ){
   
        Nog::M($i, ' out of ', $i);
        recursive($x);
    }

    Nog::C();
}

//Lets run some code:

create_some_classes();

Nog::A('recursive');

recursive(4);

Nog::A('the end');

Nog::X();

Echo "Done: ".time();

//These calls will be ignored.

Nog::O();
Nog::C();