<?php

$messages_buffer_file = 'messages.json';

$messages_buffer_size = 50;

if ( isset($_POST['content']) and isset($_POST['name']) )
{
    
    $buffer = fopen($messages_buffer_file, 'r+b');
    flock($buffer, LOCK_EX);
    $buffer_data = stream_get_contents($buffer);
    
    
    $messages = $buffer_data ? json_decode($buffer_data, true) : array();
    $next_id = (count($messages) > 0) ? $messages[count($messages) - 1]['id'] + 1 : 0;
    $messages[] = array('id' => $next_id, 'time' => time(), 'name' => $_POST['name'], 'content' => $_POST['content']);
    
    
    if (count($messages) > $messages_buffer_size)
        $messages = array_slice($messages, count($messages) - $messages_buffer_size);
    
    
    ftruncate($buffer, 0);
    rewind($buffer);
    fwrite($buffer, json_encode($messages));
    flock($buffer, LOCK_UN);
    fclose($buffer);
    
    exit();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Chat - Find True Love</title>
    <script type="text/javascript" src="jquery-1.4.2.min.js"></script>
    <script type="text/javascript">
        
        $(document).ready(function(){
            
            $('ul#messages > li').remove();
            
            $('form').submit(function(){
                var form = $(this);
                var name =  form.find("input[name='name']").val();
                var content =  form.find("input[name='content']").val();
                
                
                if (name == '' || content == '')
                    return false;
                
                $.post(form.attr('action'), {'name': name, 'content': content}, function(data, status){
                    $('<li class="pending" />').text(content).prepend($('<small />').text(name)).appendTo('ul#messages');
                    $('ul#messages').scrollTop( $('ul#messages').get(0).scrollHeight );
                    form.find("input[name='content']").val('').focus();
                });
                return false;
            });
            
            
            var poll_for_new_messages = function(){
                $.ajax({url: 'messages.json', dataType: 'json', ifModified: true, timeout: 2000, success: function(messages, status){
                    
                    if (!messages)
                        return;
                    
                    
                    $('ul#messages > li.pending').remove();
                    
                    var last_message_id = $('ul#messages').data('last_message_id');
                    if (last_message_id == null)
                        last_message_id = -1;
                    
                    for(var i = 0; i < messages.length; i++)
                    {
                        var msg = messages[i];
                        if (msg.id > last_message_id)
                        {
                            var date = new Date(msg.time * 1000);
                            $('<li/>').text(msg.content).
                                prepend( $('<small />').text(date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds() + ' ' + msg.name) ).
                                appendTo('ul#messages');
                            $('ul#messages').data('last_message_id', msg.id);
                        }
                    }
                    
                    $('ul#messages > li').slice(0, -50).remove();
                    $('ul#messages').scrollTop( $('ul#messages').get(0).scrollHeight );
                }});
            };
            
            
            poll_for_new_messages();
            setInterval(poll_for_new_messages, 2000);
        });
        
    </script>
    <style type="text/css">
        html { margin: 0em; padding: 0; }
        body {  padding: 15px; margin-bottom: 15px; width: 100%; padding: 12px 20px; margin: 8px 0;  box-sizing: border-box;  }
        .header { margin:auto; margin-top:1px; padding:1px; background-color:#fafafa; width:220px; -moz-border-radius:10px; -webkit-border-radius:10px; border-radius:10px; text-align:center; }
        ul#messages { overflow: auto; height: 15em; margin: 1em 0; padding: 0 3px; list-style: none; border: 1px solid gray;  background-color: #efefef; }
        ul#messages li { margin: 0.35em 0; padding: 0; }
        ul#messages li small { display: block; font-size: 1.0em; color: gray; }
        ul#messages li.pending { color: #aaa; }
        form { font-size: 1em; margin: 1em 0; padding: 0; }
        form p { position: relative; margin: 0.5em 0; padding: 0; }
        form p input { font-size: 1em; }
        form p input#name { max-width: auto; }
        form p button { position: absolute; top: 0; right: -0.5em; }
        ul#messages, form p, input#content { max-width: auto; }
        input#content {  width: 100%; padding: 12px 20px; margin: 8px 0; box-sizing: border-box; }
        fieldset { border: 1px solid gray; }
        input[type=text] { width: 150px; padding: 10px 20px; margin: 8px 0; box-sizing: border-box; }
		button[type=submit] { display: inline-block; border-radius: 4px; background-color: #008f00; border: none; color: #FFFFFF; text-align: center; font-size: 14px; padding: 10px; width: auto; transition: all 0.5s; cursor: pointer; margin: 10px;  }
    </style>
    <meta name="author" content="merabi" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="shoutbox.eu.org - &#8470; 1 best mini chat room.">
  <meta name="keywords" content="chati, cheti, gacnoba, ჩატი, ჩეთი, გაცნობა, chat, dating, meet">
<script type="text/javascript">
   function addTextTag(text){
    document.getElementById('content').value += text;
   }        
</script>
<script type="text/javascript">
function chBackcolor(color) {
   document.body.style.background = color;
}       
</script>
</head>
<body>

<center><div class="header"><img src="img/logo.png" style="width:100%;" alt="shoutbox.eu.org"></div></center><br>

<ul id="messages">
    <li>Loading… please wait</li>
</ul>

<form action="<?= htmlentities($_SERVER['PHP_SELF'], ENT_COMPAT, 'UTF-8'); ?>" method="post">
    <p>
        <input type="text" name="content" id="content" maxlength="500" placeholder="Type a message.."/>
    </p>
    <p>
        <label>
            <input type="text" name="name" id="name" maxlength="50" placeholder="Nickname" />
        </label>
        <button type="submit"> Send </button>
    </p>
</form>
<center><fieldset>
    <legend><font color="#888888">Smileys</font></legend>
<a href="javascript:void(0)" onClick="addTextTag('&#128512;'); return false">&#128512;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128514;'); return false">&#128514;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128515;'); return false">&#128515;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128516;'); return false">&#128516;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128517;'); return false">&#128517;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128518;'); return false">&#128518;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128519;'); return false">&#128519;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128520;'); return false">&#128520;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128521;'); return false">&#128521;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128522;'); return false">&#128522;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128523;'); return false">&#128523;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128524;'); return false">&#128524;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128525;'); return false">&#128525;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128526;'); return false">&#128526;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128527;'); return false">&#128527;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128528;'); return false">&#128528;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128529;'); return false">&#128529;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128530;'); return false">&#128530;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128531;'); return false">&#128531;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128532;'); return false">&#128532;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128533;'); return false">&#128533;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128534;'); return false">&#128534;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128535;'); return false">&#128535;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128536;'); return false">&#128536;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128537;'); return false">&#128537;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128538;'); return false">&#128538;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128539;'); return false">&#128539;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128540;'); return false">&#128540;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128541;'); return false">&#128541;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128542;'); return false">&#128542;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128543;'); return false">&#128543;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128544;'); return false">&#128544;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128545;'); return false">&#128545;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128546;'); return false">&#128546;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128547;'); return false">&#128547;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128548;'); return false">&#128548;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128549;'); return false">&#128549;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128550;'); return false">&#128550;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128551;'); return false">&#128551;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128552;'); return false">&#128552;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128553;'); return false">&#128553;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128554;'); return false">&#128554;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128555;'); return false">&#128555;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128556;'); return false">&#128556;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128557;'); return false">&#128557;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128558;'); return false">&#128558;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128559;'); return false">&#128559;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128560;'); return false">&#128560;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128561;'); return false">&#128561;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128562;'); return false">&#128562;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128563;'); return false">&#128563;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128564;'); return false">&#128564;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128565;'); return false">&#128565;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128566;'); return false">&#128566;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128567;'); return false">&#128567;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128568;'); return false">&#128568;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128569;'); return false">&#128569;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128570;'); return false">&#128570;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128571;'); return false">&#128571;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128572;'); return false">&#128572;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128573;'); return false">&#128573;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128574;'); return false">&#128574;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128575;'); return false">&#128575;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128576;'); return false">&#128576;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128577;'); return false">&#128577;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128578;'); return false">&#128578;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128579;'); return false">&#128579;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128580;'); return false">&#128580;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129296;'); return false">&#129296;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129297;'); return false">&#129297;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129298;'); return false">&#129298;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129299;'); return false">&#129299;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129300;'); return false">&#129300;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129301;'); return false">&#129301;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129312;'); return false">&#129312;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129313;'); return false">&#129313;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129314;'); return false">&#129314;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129315;'); return false">&#129315;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129316;'); return false">&#129316;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129317;'); return false">&#129317;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129318;'); return false">&#129318;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129319;'); return false">&#129319;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129320;'); return false">&#129320;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129321;'); return false">&#129321;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129322;'); return false">&#129322;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129323;'); return false">&#129323;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129324;'); return false">&#129324;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129325;'); return false">&#129325;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129326;'); return false">&#129326;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129327;'); return false">&#129327;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129488;'); return false">&#129488;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#129311;'); return false">&#129311;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128077;'); return false">&#128077;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128078;'); return false">&#128078;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128075;'); return false">&#128075;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128150;'); return false">&#128150;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#128156;'); return false">&#128156;</a>
<a href="javascript:void(0)" onClick="addTextTag('&#9995;'); return false">&#9995;</a>
</fieldset><br><br>
	<a href="javascript:void(0)" onclick="chBackcolor('white');"><img src="img/color/white.png" alt="white"></a>
	<a href="javascript:void(0)" onclick="chBackcolor('red');"><img src="img/color/red.png" alt="red"></a>
	<a href="javascript:void(0)" onclick="chBackcolor('green');"><img src="img/color/green.png" alt="green"></a>
	<a href="javascript:void(0)" onclick="chBackcolor('blue');"><img src="img/color/blue.png" alt="blue"></a>
	<a href="javascript:void(0)" onclick="chBackcolor('yellow');"><img src="img/color/yellow.png" alt="yellow"></a>
	<a href="javascript:void(0)" onclick="chBackcolor('black');"><img src="img/color/black.png" alt="black"></a><br>
</center>
<?php include "banner.php"; ?>
</body>
</html>
