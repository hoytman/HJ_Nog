<?php 

class Nog{
    
    public static $ON = true;
    public static $path = "nog/";
    
    public static $file;
  
    public static $func_width_by_level = array(0 => 0);
    public static $current_level = 0;
    
    public static $last_time = 0;
    public static $time_stack = array();
    public static $name_stack = array();
    
    public static $index = 0;
    
    
    /*
     * This is the constructor / init function
     * Pass in a string to ba used in the file name 
     * 
     * Also, change self::$path to alter the dir the files
     * are stored in.  The current dir makes use of 
     * a Codeigniter constant. 
     * 
     */
    
    public function __construct($name = 'nog'){
        if(!self::$ON){return;}
        self::init($name);
        
    }
    
    public static function init($name = 'nog'){
        if(!self::$ON){return;}

        if(is_array($name)){
            $name = $name[0];
        }

        if(!file_exists (self::$path)){
            mkdir(self::$path);
        }

        $filename = $name .'__'. date('Y_m_d__G_i_s').'.html';
        self::$file = fopen(self::$path.'/'.$filename, 'a');
        
        $date = date('g:i:sA F j, Y');
        
        fwrite(self::$file, "<html><head><title>Elog Report - $name - $date</title>");

        fwrite(self::$file, "<script>".
        
        "function tog(obj, op) {".
	"var item = document.getElementsByClassName(obj);".
        "for(var i=0; i<item.length; i++){".
	"if((item[i].style.display == 'none' && op == 't')|| op == 's')".
        "{ item[i].style.display = 'block'; }".
	"else { item[i].style.display = 'none';}}}</script>".PHP_EOL);
    
        fwrite(self::$file, "<style type='text/css'>".PHP_EOL);
         
        fwrite(self::$file, "body{font-family: arial;}".PHP_EOL);
        
        fwrite(self::$file, "button{float: right}".PHP_EOL);
        
        fwrite(self::$file, ".holder{border: solid 1px black; margin:4px; margin-bottom:10px;}".PHP_EOL);
        
        fwrite(self::$file, ".title{margin: 10px; margin-bottom: 0px; padding:2px; background: black; color:white}".PHP_EOL);

        fwrite(self::$file, ".att{margin: 10px; margin-top: 0px; background: black; color:white; padding: 2px;}".PHP_EOL);
        
        fwrite(self::$file, "ul{overflow:hidden; margin: 0px; padding: 2px;}".PHP_EOL);
        
        fwrite(self::$file, "li{border-top: solid 1px black; position:relative}".PHP_EOL);
        
        fwrite(self::$file, ".obj{margin: 1px; background: white; vertical-align:middle; display:inline-block; padding: 2px; border: dashed 1px black}".PHP_EOL);
        
        fwrite(self::$file, ".bg00{background:#eee;}".PHP_EOL.".bg01{background:#bbb;}".PHP_EOL.".bg10{background:#fee;}".PHP_EOL.".bg11{background:#fbb;}".PHP_EOL.".bg20{background:#efe;}".PHP_EOL.".bg21{background:#bfb;}".PHP_EOL.".bg30{background:#eef;}".PHP_EOL.".bg31{background:#bbf;}".PHP_EOL.".bg40{background:#ffe;}".PHP_EOL.".bg41{background:#ffb;}".PHP_EOL.".bg50{background:#eff;}".PHP_EOL.".bg51{background:#bff;}".PHP_EOL.".bg60{background:#fef;}".PHP_EOL.".bg61{background:#fbf;}".PHP_EOL);
       
        fwrite(self::$file, ".time{position:absolute; bottom: 0px; right: 5px; color:#0a0; text-aligh:right}".PHP_EOL);
        
        fwrite(self::$file, "pre{white-space: pre-wrap; white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word; }".PHP_EOL);
       
        fwrite(self::$file, "</style></head><body>".PHP_EOL);
                
        fwrite(self::$file, "<button onclick=\"tog('att', 'h')\">Hide Arguments</button><button onclick=\"tog('att', 's')\">Show Arguments</button><button onclick=\"tog('list', 'h')\">Hide List</button><button onclick=\"tog('list', 's')\">Show List</button>");
             
        fwrite(self::$file, "<h1>Elog Report - $name - $date</h1>".PHP_EOL."<h3>Filename: $filename</h3>".PHP_EOL."<h3>URL: ".$_SERVER['REQUEST_URI']."</h3>".PHP_EOL);
             
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
        if(!self::$ON){return;}
        $current_time = (int)(microtime(true)*1000000);
        
        $elapsed_time = $current_time - self::$last_time;
        self::$time_stack[self::$current_level] += $elapsed_time;
        
        self::$func_width_by_level[self::$current_level] += 1;
        $func_width = self::$func_width_by_level[self::$current_level] % 2;

        self::$current_level += 1;        
        self::$func_width_by_level[self::$current_level] = 0;
        $func_level = self::$current_level % 6;
        
        self::$time_stack[self::$current_level] = 0;
        
        $time = debug_backtrace(true);

        $Class = "...";

        if(isset($time[1]['class'])){
            $Class = $time[1]['class'] . $time[1]['type'];
        }

        $function = $time[1]['function'];
        $arguments = $time[1]['args'];
        
        $name = "$Class :: $function()";
        
        self::$name_stack[self::$current_level] = $name;
        
        $i = self::$index;
        
        $output = "";

        $output .= "<div class='holder bg{$func_level}{$func_width}'>".PHP_EOL;
        
        $output .= "<button onclick=\"tog('l$i', 't')\">LIST</button><button onclick=\"tog('p$i', 't')\">ARG</button>";
        
        $output .= "<h3 class='title'> $name</h3>".PHP_EOL;

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
        if(!self::$ON){return;}
        $current_time = (int)(microtime(true)*1000000);
        $elapsed_time = $current_time - self::$last_time;
        self::$time_stack[self::$current_level] += $elapsed_time;
        
        $total_time = self::$time_stack[self::$current_level];
        
        $output = "";
        
        foreach(func_get_args() as $entry){
           
            if(is_float($entry) || is_int($entry) || is_string($entry)){
                
                $output .= htmlspecialchars($entry);
                
            }else if(is_bool($entry)){
                
                if($entry){
                    $output .= "TRUE";
                }else{
                    $output .= "FALSE";
                }
                
            }else{
                $output .= "<pre class='obj'>".htmlspecialchars(print_r($entry, true))."</pre>";
            }
            
            $output .= ' ';

        }
        
        if(substr($output, -2, 1) != ':'){

            $output .= "<div class='time'>".PHP_EOL;
            $output .= "E:{$elapsed_time}ms T:{$total_time}ms".PHP_EOL;
            $output .= "</div>".PHP_EOL;
            $output .= "</li>".PHP_EOL;
            $output .= "<li>".PHP_EOL;
        
        }
        
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
        if(!self::$ON){return;}
        
        $current_time = (int)(microtime(true)*1000000);
        $elapsed_time = $current_time - self::$last_time;
        self::$time_stack[self::$current_level] += $elapsed_time;
        
        $total_time = self::$time_stack[self::$current_level];
        $name = self::$name_stack[self::$current_level];
        
        self::$current_level -= 1;
        
        self::$time_stack[self::$current_level] += $total_time;
        
        $output = "END OF FUNCTION: $name";
        $output .= "<div class='time'>".PHP_EOL;
        $output .= "E:{$elapsed_time}ms T:{$total_time}ms".PHP_EOL;
        $output .= "</div>".PHP_EOL;
        $output .= "</li></ul></div>".PHP_EOL;

        fwrite(self::$file, $output);
        self::$last_time = (int)(microtime(true)*1000000);

    }
    
    
    
}