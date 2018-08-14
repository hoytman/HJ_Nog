<?php 

/**
 *
 * This content is released under the MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package         HJ_Nog
 * @author          Hoyt Jolly
 * @license         https://opensource.org/licenses/MIT	
 * @link	
 * @filesource
 */

    /*
     * HJ_Nog creates log files for your code executions. It has three static 
     * function calls that can be added to the start, middle and end of your 
     * functions. Nog then creates a log file upon execution which maps how 
     * the functions call each other. Useful during long debugging adventures 
     * when echo is not enough and debug database tables are not ideal.
     * 
     * Step 1: add HJ_Nog to your project using: require_once("HJ_Nog.php");
     * Step 2: add "HJ_Nog::init($path, $nane);" and pass a path for a log folder
     * Step 2.5: set the correct file permissions for that folder
     * Step 3: add "HJ_Nog::O();" to the beginning of each function.
     * Step 4: add "HJ_Nog::C();" to the end of each function, just before return.
     * Step 5: add "HJ_Nog::M();" anwhere you want to add a debug message
     *      Note: HJ_Nog::M() can accept a variety of data types.
     * Step 6: (Optional) add "HJ_Nog::X();" to the very end of your code
     * Step 7: run your code
     * 
     * Every time you run your code, HJ_Nog will save an HTML Log file which 
     * reveal how you code is running
     * 
     * note: it's best the wrap each function call in class_exists()
     * 
     *      example: if(class_exists('HJ_Nog')){HJ_Nog::M();}
     * 
     * This will allow you to easily deactiveate Nog by not including it.
     * However, if you are ok with a slower exicution time, you can use the
     * static::$ON variable to deactivate the class too.
     * 
     * Note Nog is short for "Nested Log".
     * 
     */


class HJ_Nog{
    
    /**
    * Setting this to false will disable the class so it does nothing
    *
    * @var	string
    */
    public static $ON = true;       
    
    /**
    * An internal link to the file that stores the log
    *
    * @var	string
    */
    public static $file;            
    
    /**
    * As log traverses nested functions, this is the current function call depth
    *
    * @var	string
    */
    public static $current_level = -1;
    
    /**
    * counts the number of report blocks.  Used for Javascript functions.
    *
    * @var	string
    */
    public static $block_count = 0;
    
    /**
    * counts the number of times A() has been called.  Used to create anchor
    * tags that point to the correct place in the log file. 
    *
    * @var	string
    */
    public static $anchor_count = 0;
    
    /**
    * counts the function calls per level.  Used to alternate the color 
    * brightness for report blocks.
    *
    * @var	string
    */
    public static $alternate_color = array(0 => 0);
    
    /**
    * Tracks the amount of time spent on each function.
    *
    * @var	string
    */
    public static $time_stack = array();
    
    /**
    * Tracks the function names.
    *
    * @var	string
    */
    public static $name_stack = array();
    
    /**
    * Tracks the number of times that a function has been called.
    *
    * @var	string
    */
    public static $function_list = array();
    
    
    /**
    * the last recorded time.  Used to track all 'elapsed time' values
    *
    * @var	string
    */
    public static $last_time = 0;
    
    
    /**
    * Class Constructor
    *
    * Although this is a static class, it has a constructor just incase
    * it is called by a framework or helper. 
    *  
    *
    * @param	int	$server_key     identifier for the server
    *
    * @return	none
    */
    public function __construct($path='nog', $name = 'nog', $on = true){
        
        //Exit if the class is disabled
        if(!self::$ON){return;}
        self::init($path, $name);
        
    }
    
    /**
    * init()
    *
    * Sets up the initial values, like a constructor would
    *  
    *
    * @param	String	$path   the path to the save file folder
    * @param    String  $name   the name prefix to be used for files
    * @param    boolean $on     activates or deactivates the class
    *
    * @return	none
    */
    public static function init($path='nog', $name = 'nog', $on = true){

        //Exit if the class is disabled
        if(!self::$ON){return;}
        
        self::$ON = $on;

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
        self::$file = fopen($filename, 'w');
        
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
                
             "function lev(num) {".PHP_EOL. 
                "for(var j=0; j<=num; j++){".PHP_EOL.
                    "var item = document.getElementsByClassName('level'+j);".PHP_EOL.
                    "for(var i=0; i<item.length; i++){".PHP_EOL.
                    "item[i].classList.add('reduced');".PHP_EOL.
                
                
                "}}}".PHP_EOL.
                
              "function unlev() {".PHP_EOL. 
                "var item = document.getElementsByClassName('reduced');".PHP_EOL.
                "for(var i=0; i<item.length; i++){".PHP_EOL.
                "item[i].classList.remove('reduced');".PHP_EOL.
                
                
              "}}".PHP_EOL.  

        "</script>".PHP_EOL.
    
        "<style type='text/css'>".PHP_EOL.
         
        "body{font-family: arial;}".PHP_EOL.
        
        "button{float: right; }".PHP_EOL.
                
        ".holder{ border: solid 1px black; margin:4px; margin-bottom:10px; border-right: solid 0px black; margin-right:0px;}".PHP_EOL.
                
        "div.selected{border: dashed 10px black; padding:35px; margin:15px}".PHP_EOL.
        
        ".title{margin: 10px; margin-bottom: 0px; padding:2px; background: black; color:white}".PHP_EOL.

        ".att{margin: 10px; margin-top: 0px; background: black; color:white; padding: 2px;}".PHP_EOL.
        
        "ul{overflow:hidden; margin: 0px; padding: 0px;}".PHP_EOL.
        
        "li{border-top: solid 1px black; position:relative}".PHP_EOL.
                
        ".reduced{border-left: solid 0px black;  padding-left:0px; margin-left:0px;}".PHP_EOL.
        
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
                
        "<button onclick=\"tog('att', 'h')\">Hide All Arguments</button><button onclick=\"tog('att', 's')\">Show All Arguments</button><button onclick=\"unlev()\">UnLevel All</button><button onclick=\"tog('list', 's')\">UnMinimize All</button>".
             
        "<h1>Nog Report - $date</h1>".PHP_EOL.
                
        "<h3>My Filename: $filename</h3>".PHP_EOL.
         
        "<div id='anchors'></div>".PHP_EOL.
        
        "<div id='pulled'></div>");
             
        //Setup the time tracking array
        
        self::$time_stack[0] = 0;
        
        self::$last_time = (int)(microtime(true)*1000000);
        
        self::O();
        
    }
    
    /**
     * O()
     * 
     * Call this function at the start of each function that you want to log
     * recommended syntax:
     * 
     * if(class_exists('HJ_Nog')){HJ_Nog::O();}
     * 
     * @param   String  $css    override css rules for this report block
     * 
     * @return  void
     */
    public static function O($css=""){
        
        //Exit if the class is disabled
        if(!self::$ON){return;}
       
        self::$block_count += 1;
        self::$current_level += 1;
        
        $level = self::$current_level;
        
        self::$time_stack[$level] = 0;
        
        //Track the bg color of the current function level
        if(isset(self::$alternate_color[$level]) && 
                self::$alternate_color[$level]){
            self::$alternate_color[$level] = 0;
        }else{
            self::$alternate_color[$level] = 1;
        }
        
        $num_b = self::$alternate_color[$level];

        $num_a = $level % 6;
        
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
        
        
        $line = $time[1]['line'];
       
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
        self::$name_stack[$level] = $name;
        
        //Build Log file HTML
        $i = self::$block_count;
        
        $output = "";

        $output .= "<div style='$css' class='holder $css_bg_class h$i level$level'>".PHP_EOL;
                $output .= "<button onclick=\"tog('l$i', 't')\">Minimize</button>";
                
        $output .= "<button onclick=\"tog('p$i', 't')\">Arguments</button>";
        
        $output .= "<button onclick=\"sel('h$i')\">Highlight</button>";
        
        $output .= "<button onclick=\"lev($level)\">Level</button>";

        $output .= "<h3 class='title'> $name$note</h3>".PHP_EOL;
        

        
        $output .= "<pre class='att p$i'>".htmlspecialchars(print_r($arguments, true))."</pre>";

        $output .= "<ul class='l$i list'>".PHP_EOL;
        $output .= "<li>".PHP_EOL;
        
    
        
        

        
        fwrite(self::$file, $output);
        
        self::$last_time = (int)(microtime(true)*1000000);

    }
    
    /**
     * M()
     * 
     * Adds a message to the current report block.  Used for adding debug data.
     * This function can accept several different types of parameters:
     * strings and number will simply be added.
     * Booleans will be converted to TRUE or FALSE
     * Arrays and objects will be displayed using print_r();
     * multiple parameters will be displayed, separated by spaces. 
     * 
     * recommended syntax:
     * 
     * if(class_exists('HJ_Nog')){HJ_Nog::M();}
     * 
     * @param   ---     ---       multiple parameters of different types 
     * 
     * @return  void
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
    
    /**
     * C()
     * 
     * Call this function at the end of each function you log
     * 
     * recommended syntax:
     * 
     * if(class_exists('Nog')){Nog::C();}
     * 
     * @return  void
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
            $output .= "The End</body></html>";
            fwrite(self::$file, $output);
            fclose(self::$file);
            self::$ON = false;
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
    
    
    /**
     * A()
     * 
     * Call this function at add an anchor tag to your log file.
     * Makes it easer to find specific occurrences within the file.
     * 
     * recommended syntax:
     * 
     * if(class_exists('Nog')){Nog::A();}
     * 
     * @param   String  $name   The name used for the anchor tag
     * 
     * @return  void
     */
    public static function A($name){
                
        //Exit if the class is disabled
        if(!self::$ON){return;}
        
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
    
    /**
     * X()
     * 
     * Used to immediately close all report blocks and terminate 
     * the log file at any point.  This can be used in combination with 
     * Nog::ON = true; to create a shortened file that only focuses on 
     * a specific part of your code.
     * 
     * recommended syntax:
     * 
     * if(class_exists('Nog')){Nog::X();}
     * 
     * @return  void
     */
    public static function X(){
        while(self::$ON){
            self::C();
        }
    }


}