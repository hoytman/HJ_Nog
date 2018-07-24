<?php 

class Nog{
    
    public static $ON = true;       //setting this to false shuts off the class
    public static $path = "nog/";   //the floder to whihc log files are saved
    
    public static $file;            //link to the file whihc is created
  
    public static $func_width_by_level = array(0 => 0);
    public static $current_level = 0;
    
    public static $last_time = 0;
    public static $time_stack = array();
    public static $name_stack = array();
    
    public static $index = 0;
    
    
    /*
     * This is the class constructor.  Though the class is intended to be 
     * referenced as a static class, it can still be instantiated by
     * other classes.
     * 
     * Also, change self::$path to alter the dir the files
     * are stored in.  The current dir makes use of 
     * a Codeigniter constant. 
     * 
     */
    
    public function __construct($name = 'nog'){
        
        //Exit if the class is disabled
        if(!self::$ON){return;}
        self::init($name);
    }
    
    public static function init($name = 'nog'){
        
        //Exit if the class is disabled
        if(!self::$ON){return;}

        if(is_array($name)){
            $name = $name[0];
        }
        
        //Create the Log file

        if(!file_exists (self::$path)){
            mkdir(self::$path);
        }

        $filename = $name .'__'. date('Y_m_d__G_i_s').'.html';
        self::$file = fopen(self::$path.'/'.$filename, 'a');
        
        //Add the HTML head for the log file
        
        $date = date('g:i:sA F j, Y');
        
        fwrite(self::$file, 
        
        "<html>".PHP_EOL.
        "<head>".PHP_EOL.
        "<title>$name - $date</title>".PHP_EOL.
                
        "<script>".PHP_EOL.
            "function tog(obj, op) {".PHP_EOL.
            "var item = document.getElementsByClassName(obj);".PHP_EOL.
            "for(var i=0; i<item.length; i++){".PHP_EOL.
            "if((item[i].style.display == 'none' && op == 't')|| op == 's')".PHP_EOL.
            "{ item[i].style.display = 'block'; }".PHP_EOL.
            "else { item[i].style.display = 'none';}}}".PHP_EOL.
        "</script>".PHP_EOL.
    
        "<style type='text/css'>".PHP_EOL.
         
        "body{font-family: arial;}".PHP_EOL.
        
        "button{float: right}".PHP_EOL.
        
        ".holder{border: solid 1px black; margin:4px; margin-bottom:10px;}".PHP_EOL.
        
        ".title{margin: 10px; margin-bottom: 0px; padding:2px; background: black; color:white}".PHP_EOL.

        ".att{margin: 10px; margin-top: 0px; background: black; color:white; padding: 2px;}".PHP_EOL.
        
        "ul{overflow:hidden; margin: 0px; padding: 2px;}".PHP_EOL.
        
        "li{border-top: solid 1px black; position:relative}".PHP_EOL.
        
        ".obj{margin: 1px; background: white; vertical-align:middle; display:inline-block; padding: 2px; border: dashed 1px black}".PHP_EOL.
        
        ".bg00{background:#eee;}".PHP_EOL.
        
        ".bg01{background:#bbb;}".PHP_EOL.
                
        ".bg10{background:#fee;}".PHP_EOL.
             
        ".bg11{background:#fbb;}".PHP_EOL.
                
        ".bg20{background:#efe;}".PHP_EOL.
                
        ".bg21{background:#bfb;}".PHP_EOL.
                
        ".bg30{background:#eef;}".PHP_EOL.
                
        ".bg31{background:#bbf;}".PHP_EOL.
                
        ".bg40{background:#ffe;}".PHP_EOL.
                
        ".bg41{background:#ffb;}".PHP_EOL.
                
        ".bg50{background:#eff;}".PHP_EOL.
                
        ".bg51{background:#bff;}".PHP_EOL.
                
        ".bg60{background:#fef;}".PHP_EOL.
                
        ".bg61{background:#fbf;}".PHP_EOL.
       
        ".time{position:absolute; bottom: 0px; right: 5px; color:#0a0; text-aligh:right}".PHP_EOL.
        
        "pre{white-space: pre-wrap; white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word; }".PHP_EOL.
       
        "</style>".PHP_EOL.
                
        "</head>".PHP_EOL.
                
        "<body>".PHP_EOL.
                
        "<button onclick=\"tog('att', 'h')\">Hide Arguments</button><button onclick=\"tog('att', 's')\">Show Arguments</button><button onclick=\"tog('list', 'h')\">Hide List</button><button onclick=\"tog('list', 's')\">Show List</button>".
             
        "<h1>Elog Report - $name - $date</h1>".PHP_EOL.
                
        "<h3>Filename: $filename</h3>".PHP_EOL.
                
        "<h3>URL: ".$_SERVER['REQUEST_URI']."</h3>".PHP_EOL);
             
        //Setup the time tracking array
        
        self::$time_stack[0] = 0;
        
        self::$last_time = (int)(microtime(true)*1000000);
    }
    
    /*
     * Call this function at the start of a function you want to log
     * the syntex should be:
     * 
     * if(class_exists('Nog')){Nog::O();}
     * 
     */
    
    public static function O(){
        
        //Exit if the class is disabled
        if(!self::$ON){return;}
       
        //Setup the time tracking array for this function level
        self::$time_stack[self::$current_level] = 0;
        
        //Track the bg color of the current function level
        self::$func_width_by_level[self::$current_level] += 1;
        $num_b = self::$func_width_by_level[self::$current_level] % 2;

        self::$current_level += 1;        
        self::$func_width_by_level[self::$current_level] = 0;
        $num_a = self::$current_level % 6;
        
        $css_bg_class = 'bg'.$num_a.$num_b;
        
        //Get function and class information
        $time = debug_backtrace(true);

        $Class = "* ";
        $function = "...";
                
        if(isset($time[1]['class'])){
            $Class = $time[1]['class'] .' '. $time[1]['type'] . ' ';
        }
        
        if($time[1]['function']){
            $function = $time[1]['function'].'()';
        }
        
        
        $file = $time[1]['file'];
        $line = $time[1]['line'];
        $arguments = $time[1]['args'];
        
  
        
        $name = "$Class$function";
        $note = "<div style='float:right'>$file:$line</div>";
    
        //Track function name for function closure
        self::$name_stack[self::$current_level] = $name;
        
        //Build Log file HTML
        $i = self::$index;
        
        $output = "";

        $output .= "<div class='holder $css_bg_class'>".PHP_EOL;
        
        $output .= "<button onclick=\"tog('l$i', 't')\">LIST</button><button onclick=\"tog('p$i', 't')\">ARG</button>";
        
        $output .= "<h3 class='title'> $name$note</h3>".PHP_EOL;

        $output .= "<pre class='att p$i'>".htmlspecialchars(print_r($arguments, true))."</pre>";

        $output .= "<ul class='l$i list'>".PHP_EOL;
        $output .= "<li>".PHP_EOL;
        
        self::$index += 1;
        fwrite(self::$file, $output);
        self::$last_time = (int)(microtime(true)*1000000);

    }
    
    /*
     * Call this function to log a message
     * the syntex should be:
     * 
     * if(class_exists('Nog')){Nog::M('...');}
     * 
     * the function can take a String, int, float, boolean, array, object
     * Multiple paramaters can be passed
     * 
     * Also, if a string endign with ':' is passed
     * then the string will be used as a label
     * 
     */
    
    public static function M(){
        
        //Exit if the class is disabled
        if(!self::$ON){return;}
        
        //Track Time
        $current_time = (int)(microtime(true)*1000000);
        $elapsed_time = $current_time - self::$last_time;
        self::$time_stack[self::$current_level] += $elapsed_time;
        
        $total_time = self::$time_stack[self::$current_level];
        
       //Build Log file HTML
        $output = "";
        
        foreach(func_get_args() as $entry){
           
            if(is_float($entry) || is_int($entry) || is_string($entry)){
                
                //Simply display these vars as they are
                $output .= htmlspecialchars($entry);
                
            }else if(is_bool($entry)){
                
                //Display Booleans 
                if($entry){
                    $output .= "TRUE";
                }else{
                    $output .= "FALSE";
                }
                
            }else{
                
                //Every other object should be displayed with <pre>
                $output .= "<pre class='obj'>".htmlspecialchars(print_r($entry, true))."</pre>";
            }
            
            $output .= ' ';

        }

        $output .= "<div class='time'>".PHP_EOL;
        $output .= "time+{$elapsed_time}ms={$total_time}ms".PHP_EOL;
        $output .= "</div>".PHP_EOL;
        $output .= "</li>".PHP_EOL;
        $output .= "<li>".PHP_EOL;
        
        fwrite(self::$file, $output);
        self::$last_time = (int)(microtime(true)*1000000);
    }
    
    
    /*
     * Call this function at the end of a function you want to log
     * the syntex should be:
     * 
     * if(class_exists('Nog')){Nog::C();}
     * 
     */
    
    public static function C(){
        
        //Exit if the class is disabled
        if(!self::$ON){return;}
        
        //Track Time
        $current_time = (int)(microtime(true)*1000000);
        $elapsed_time = $current_time - self::$last_time;
        self::$time_stack[self::$current_level] += $elapsed_time;
        
        $total_time = self::$time_stack[self::$current_level];
        $name = self::$name_stack[self::$current_level];
        
        self::$current_level -= 1;
        
        //Add elapsed time to the total time of the previous function
        self::$time_stack[self::$current_level] += $total_time;
        
        //Build Log file HTML
        $output = "END OF FUNCTION: $name";
        $output .= "<div class='time'>".PHP_EOL;
        $output .= "time+{$elapsed_time}ms={$total_time}ms".PHP_EOL;
        $output .= "</div>".PHP_EOL;
        $output .= "</li></ul></div>".PHP_EOL;

        fwrite(self::$file, $output);
        self::$last_time = (int)(microtime(true)*1000000);

    }

}