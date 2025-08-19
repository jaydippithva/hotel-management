 <?php
    require 'inc/sendgrid-php/vendor/autoload.php';
    if(require("inc/sendgrid-php/vendor/autoload.php"))
        {echo "path found";}

    sendemail('kanwararyan2@gmail.com','SEndgrid','kanwararyan1@gmail.com','HI');
    function sendemail($f,$s,$t,$m){

    $from = new \SendGrid\Email(null, $f);
    
    $subject = $s;
    $to = new \SendGrid\Email(null, $t);
    $content = new \SendGrid\Content("text/plain", $m);
    $mail = new \SendGrid\Mail($from, $subject, $to, $content);

    $apiKey = getenv('Xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
    $sg = new \SendGrid($apiKey);

    $response = $sg->client->mail()->send()->post($mail);
    echo $response->statusCode();
    echo $response->headers();
    echo $response->body();
}
?>