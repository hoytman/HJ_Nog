<?php 

    /*
     * Nog creates log files for your code executions. It has three static 
     * function calls that can be added to the start, middle and end of your 
     * functions. Nog then creates a log file upon execution which maps how 
     * the functions call each other. Useful during long debugging adventures 
     * when echo is not enough and debug database tables are not ideal.
     * 
     * Step 1: add Nog to your project using: require_once("Nog.php");
     * Step 2: add "Nog::init($path);" and pass a path for a log folder
     * Step 2.5: set the correct file permissions for that folder
     * Step 3: add "Nog::O();" to the beginning of each function.
     *      Note: "if(class_exists('Nog')){Nog::O();}" is prefered
     * Step 4: add "Nog::C();" to the end of each function, just before return.
     *      Note: "if(class_exists('Nog')){Nog::C();}" is prefered
     * Step 5: add "Nog::M();" anwhere you want to add a debug message
     *      Note: "if(class_exists('Nog')){Nog::M();}" is prefered
     *      Note: Nog::M() can accept a variety of data types.
     * Step 6: (Optional) add "Nog::X();" to the very end of your code
     *      Note: again, "if(class_exists('Nog')){Nog::X();}" is prefered
     * Step 7: run your code
     * 
     * Every time you run your code, Nog will save an HTML Log file which 
     * reveal how you code is running
     * 
     * note: it's best the wrap each function call in class_exists()
     * 
     *      example: if(class_exists('Nog')){Nog::M();}
     * 
     * This will allow you to easily deactiveate Nog by not including it.
     * However, if you are ok with a slower exicution time, you can use the
     * static::$ON variable to deactivate the class too.
     * 
     * Note Nog is short for "Nested Log".
     * 
     */


class Nog{
    
    public static $ON = true;       //setting this to false shuts off the class
    
    public static $file;            //link to the file whihc is created

    public static $current_level = -1;
    public static $block_count = 0;
    public static $anchor_count = 0;
    
    public static $func_width_by_level = array(0 => 0);
    public static $time_stack = array();
    public static $name_stack = array();
    public static $function_list = array();
    
    public static $last_time = 0;
    
    /*
     * This is the class constructor.  Though the class is intended to be 
     * referenced as a static class, it can still be instantiated by
     * other classes.
     * 
     */
    
    public function __construct($path='nog', $name = 'nog'){
        
        //Exit if the class is disabled
        if(!self::$ON){return;}
        self::init($name);
        
    }
    
    public static function init($path='nog', $name = 'nog'){
        
        //Exit if the class is disabled
        if(!self::$ON){return;}

        if(is_array($path)){
            if(isset($path[1])){
                $name = $path[1];
            }
            $path = $path[0];
        }
        
        if(substr($path, -1, 1) != DIRECTORY_SEPARATOR){
            $path .= DIRECTORY_SEPARATOR;
        }
        
        //Create the Log file

        if(!file_exists ($path)){
            mkdir($path);
        }

        $filename = $path.$name .'__'. date('Y_m_d__G_i_s').'.html';
        self::$file = fopen($filename, 'a');
        
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
                
            "function sel(obj) {".PHP_EOL.
            "var item = document.getElementsByClassName(obj);".PHP_EOL.
            "for(var i=0; i<item.length; i++){".PHP_EOL.
            "if(item[i].classList.contains('selected'))".PHP_EOL.
            "{ item[i].classList.remove('selected'); }".PHP_EOL.
            "else { item[i].classList.add('selected');}}}".PHP_EOL.
                
                

        "</script>".PHP_EOL.
    
        "<style type='text/css'>".PHP_EOL.
         
        "body{font-family: arial;}".PHP_EOL.
        
        "button{float: right}".PHP_EOL.
                
        ".holder{border: solid 1px black; margin:4px; margin-bottom:10px; margin-right:0px; min-width:960px}".PHP_EOL.
                
        "div.selected{border: dashed 10px black; padding:35px; margin:15px}".PHP_EOL.
        
        ".title{margin: 10px; margin-bottom: 0px; padding:2px; background: black; color:white}".PHP_EOL.

        ".att{margin: 10px; margin-top: 0px; background: black; color:white; padding: 2px;}".PHP_EOL.
        
        "ul{overflow:hidden; margin: 0px; padding: 2px;}".PHP_EOL.
        
        "li{border-top: solid 1px black; position:relative}".PHP_EOL.
        
        ".obj{margin: 1px; background: white; vertical-align:middle; display:inline-block; padding: 2px; border: dashed 1px black}".PHP_EOL.
        
        ".bg00{background:#eee;}".PHP_EOL.
        
        ".bg01{background:#bbb;}".PHP_EOL.
                
        ".bg10{background:#fdd;}".PHP_EOL.
             
        ".bg11{background:#fbb;}".PHP_EOL.
                
        ".bg20{background:#dfd;}".PHP_EOL.
                
        ".bg21{background:#bfb;}".PHP_EOL.
                
        ".bg30{background:#ddf;}".PHP_EOL.
                
        ".bg31{background:#bbf;}".PHP_EOL.
                
        ".bg40{background:#ffd;}".PHP_EOL.
                
        ".bg41{background:#ffb;}".PHP_EOL.
                
        ".bg50{background:#dff;}".PHP_EOL.
                
        ".bg51{background:#bff;}".PHP_EOL.
                
        ".bg60{background:#fdf;}".PHP_EOL.
                
        ".bg61{background:#fbf;}".PHP_EOL.
       
        ".time{position:absolute; bottom: 0px; right: 5px; color:#0a0; text-aligh:right}".PHP_EOL.
        
        "pre{white-space: pre-wrap; white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word; }".PHP_EOL.
       
        "</style>".PHP_EOL.
                
        "</head>".PHP_EOL.
                
        "<body>".PHP_EOL.
                
        "<button onclick=\"tog('att', 'h')\">Hide Arguments</button><button onclick=\"tog('att', 's')\">Show Arguments</button><button onclick=\"tog('list', 'h')\">Hide Children</button><button onclick=\"tog('list', 's')\">Show Children</button>".
             
        "<h1>Nog Report - $date</h1>".PHP_EOL.
                
        "<h3>My Filename: $filename</h3>".PHP_EOL.
         
        "<div id='anchors'></div>".PHP_EOL.
        
        "<div id='pulled'></div>");
             
        //Setup the time tracking array
        
        self::$time_stack[0] = 0;
        
        self::$last_time = (int)(microtime(true)*1000000);
        
        self::O();
        
    }
    
    /*
     * Call this function at the start of a function you want to log
     * the syntex should be:
     * 
     * if(class_exists('Nog')){Nog::O();}
     * 
     */
    
    public static function O($line=""){
        
        //Exit if the class is disabled
        if(!self::$ON){return;}
       
        self::$block_count += 1;
        self::$current_level += 1;
        
        self::$time_stack[self::$current_level] = 0;
        
        //Track the bg color of the current function level
        self::$func_width_by_level[self::$current_level] += 1;
        
        $num_b = self::$func_width_by_level[self::$current_level] % 2;

        $num_a = self::$current_level % 6;
        
        //Setup the time tracking array for this function level

        $css_bg_class = 'bg'.$num_a.$num_b;
        
        //Get function and class information
        $time = debug_backtrace(true);

        $Class = "*** ";
        $function = "---";
        $file = "line:";
        
        if(isset($time[1]['class'])){
            $Class = $time[1]['class'] .' '. $time[1]['type'] . ' ';
        }
        
        if($time[1]['function']){
            $function = $time[1]['function'].'()';
        }
        
        if($time[1]['file']){
            $file = $time[1]['file'].':';
        }
        
        if(!$line){
            $line = $time[1]['line'];
        }else{
            $line = $time[1]['line'] .":".$line;
        }
        
        $arguments = $time[1]['args'];

        $name = "$Class$function";
        $note = "<div style='float:right'>$file$line</div>";
    
        if(isset(self::$function_list[$name])){
            self::$function_list[$name]++;
        }else{
            self::$function_list[$name] = 1;
        }
        
        $name .= ' #'.self::$function_list[$name];
        
        //Track function name for function closure
        self::$name_stack[self::$current_level] = $name;
        

        
        
        
        //Build Log file HTML
        $i = self::$block_count;
        
        $output = "";

        $output .= "<div class='holder $css_bg_class h$i'>".PHP_EOL;
        
        $output .= "<button onclick=\"tog('l$i', 't')\">Children</button>";
                
        $output .= "<button onclick=\"tog('p$i', 't')\">Arguments</button>";
        
        $output .= "<button onclick=\"sel('h$i')\">Highlight</button>";
        
        $output .= "<h3 class='title'> $name$note</h3>".PHP_EOL;

        $output .= "<pre class='att p$i'>".htmlspecialchars(print_r($arguments, true))."</pre>";

        $output .= "<ul class='l$i list'>".PHP_EOL;
        $output .= "<li>".PHP_EOL;
        

        
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
        $output .= "time+{$elapsed_time}&#181;s={$total_time}&#181;s".PHP_EOL;
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
        

        
        //Build Log file HTML
        $output = "END OF FUNCTION: $name";
        
        $output .= "<div class='time'>".PHP_EOL;
        $output .= "time+{$elapsed_time}&#181;s={$total_time}&#181;s".PHP_EOL;
        $output .= "</div>".PHP_EOL;

        $output .= "</li></ul></div>".PHP_EOL;


        self::$current_level -= 1;
        
        if(self::$current_level < 0){
            fwrite(self::$file, $output);
            return;
        }
        
        

        
        //Add elapsed time to the total time of the previous function
        self::$time_stack[self::$current_level] += $total_time;
        
        $parent_name = self::$name_stack[self::$current_level];
        $parent_total_time =  self::$time_stack[self::$current_level];
        
        $output .= " resume: $parent_name";
        $output .= "<div class='time'>".PHP_EOL;
        $output .= "time+{$total_time}&#181;s={$parent_total_time}&#181;s".PHP_EOL;
        $output .= "</div>".PHP_EOL;
        $output .= "</li>".PHP_EOL;
        $output .= "<li>".PHP_EOL;
        
        fwrite(self::$file, $output);
        
        self::$last_time = (int)(microtime(true)*1000000);

    }
    
    public static function A($name){
        
        $num = self::$anchor_count;
        
        $output = 
                
        "<a name='anc_$num'></a>".PHP_EOL.
                
        "<script>".PHP_EOL.
               
        "var txt = document.getElementById('anchors').innerHTML;".PHP_EOL.
                
        "document.getElementById('anchors').innerHTML = txt + ".
                
            "\"<div><a href='#anc_$num'>Anchor $num: $name</a></div>\"".PHP_EOL.
                
        "</script>".PHP_EOL.
                
        "<h3 style='text-align: center;'>====== Anchor $num: $name ======</h3></li><li>";
        
       
        fwrite(self::$file, $output);

        self::$anchor_count++;
        
    }
    
    public static function X(){
        self::C();
        fwrite(self::$file, "The End</body></html>");
    }

}