<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo $settings->site_name;?></title>
    </head>
    <body style="font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 14px; line-height: 1.5; padding: 0; margin: 0; background-color: #f1f0ec;">
        <div style="color:#252525; background-color:#FFF; width: 640px; margin: 0 auto; padding: 15px;">
            <table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
                <tbody>
                    <tr>
                        <td style="padding:15px 0 15px 0; border-bottom:1px solid #E1E3E4;">
                            <img src="<?php echo $settings->site_logo;?>" alt="<?php echo $settings->site_name;?>" title="<?php echo $settings->site_name;?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>Hello <?php echo $visitor->name;?>,</p>
                        </td>
                    </tr>  
                    <tr>
                        <td>
                            <p>We are attaching your chat current chat history with this email.</p>
                            <?php foreach ($chatHistory as $row):?>
                                <div>
                                    <?php 
                                    if($row->message_type == 'file') {
                                        $file_object = json_decode($row->message_meta);
                                        $chat_message = file_link($row->chat_message, $file_object->filesize, $file_object->filename);                                        
                                    } else {
                                        $chat_message = $row->chat_message;
                                    }
                                    ?>
                                    <p><strong><?php echo $row->name;?></strong> : <?php echo $chat_message;?></p>
                                </div>
                            <?php endforeach;?>
                        </td>
                    </tr>  
                </tbody>
            </table>
        </div>
    </body>
</html>