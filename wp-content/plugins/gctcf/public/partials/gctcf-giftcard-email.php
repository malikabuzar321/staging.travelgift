<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>New giftcard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<style>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');
body {
	font-family: 'Montserrat', sans-serif;
}
</style>
<body>
<center>
  <table width="400px" cellpadding="0" cellspacing="0" border="0" style="width: 400px;background-color: #f3f3f3; margin: auto; padding: 30px;">
    <tr>
      <td align="center" background="#f3f3f3"><!--[if gte mso 9]>
      <center>
        <table width="400" cellpadding="0" cellspacing="0" border="0" background="#f3f3f3" style="width:400px; background-color: #f3f3f3;">
          <tr>
            <td align="center" width="400" style="background-color: #f3f3f3;">
              <![endif]-->
              
              <table cellpadding="0" cellspacing="0" border="0">
              <tr>
              <td align="center" style="width:100%;text-align: center;background-color: #fff;padding-top: 30px;">
              <img src="<?php echo GCTCF_URL; ?>public/images/giftcart-logo.png" alt="image" style="width: 200px;height: 100px;object-fit: contain;">
              </td>
              </tr>
              <tr class="gc-email-giftcard-sec" style="
    width: 100%;
    text-align: center;
    float: left;
">
      <td align="center" class="gc-email-giftcard-sec-image" style="    background-color: #4ba3d3;
    padding-bottom: 22px;
    "><img src="<?php echo GCTCF_URL; ?>public/images/gift2.png" alt="image" style="
    width:420px;
    display: block;
    margin: 0 auto;
    object-fit: cover;
">
        <p style="
    color: #fff;
    font-size: 19px;
    font-weight: 300;
    margin-top: 0;
    margin-bottom: 0;
">You have been sent a gift card!</p></td>
    </tr>
    
    <tr style="background-color: #3086c5;">
    <td align="center" class="gc-price-sec" style="
    background-color: #3086c5;
    width: 100%;
    float: left;padding:30px 0px; margin:0;
">
      <P style="
    font-size: 50px;
    color: #fff;
    font-weight: 600;
    text-align: center;
">£ <?php echo $price; ?></P>
    </td>
    </tr>
           <tr style="
    background-color: #3086c5;
">
    <td align="center" style="
    text-align: center;
    font-weight: 600;
    color: #fff; padding-bottom:20px;
    font-size: 14px;
">Gift card code: <span style="
    font-weight: 400;
"><?php echo $code; ?> </span></td>
  </tr>   
           
           <tr style="
    background-color: #3086c5;
">
    <td align="center" class="gc-logo-inner-text-sec" style="
    width: 100%;
    float: left;
    text-align: center;
"><img src="<?php echo GCTCF_URL; ?>public/images/cut-top.png" alt="image" style="
    width: 200px;
    margin: 0 auto;
    margin-top: 30px;
"></td>
  </tr>  
           
  <tr style="
    background-color: #3086c5;
">
    <td align="center" style="
    text-align: center;
    color: #fff;
    margin: 14px 0px;
    line-height: 28px;
    font-size: 14px;
"><span class="im" style="color: #fff;">To <?php 	echo $recipient_name; ?><br><br>
<strong>Message</strong><br>
                  <?php echo $message; ?></span></td>
  </tr>
  <tr style="
    background-color: #3086c5;
">
    <td align="center" style="text-align: center;"><img src="<?php echo GCTCF_URL; ?>public/images/cut-bottom.png" alt="image" style="
    width: 200px;
    margin: 0 auto;
    margin-bottom: 30px;
"></td>
  </tr>
   <tr style="
    background-color: #3086c5;
">
    <td align="center" style="
    padding: 0px 20px;
    text-align: center;
    color: #fff;
    font-size: 20px;
    font-weight: 500;
    margin-bottom: 10px;
    text-transform: uppercase;
    margin-top: 25px;
">Redemption Instructions</td>
  </tr>
   <tr>
    <td align="center" style="
    padding: 0px 20px;
    text-align: center;
    color: #fff;
    font-size: 14px;
    padding-bottom: 30px;
    line-height: 24px;
    background-color: #3086c5;
    padding-top: 18px;
">It's so simple! Input the gift card code at checkout. The
      card balance will then be deducted from the shopping
      basket total. Complete your purchase and enjoy!</td>
  </tr>
  
  
  <tr class="gc-bg-image" style="
    width: 100%;
    padding:0;
    margin:0;
">
    <td align="center" style="
    margin-bottom: 0;
    line-height: 0;
    padding:0;
    margin:0;
"><img src="<?php echo GCTCF_URL; ?>public/images/gc-tour-bg-1.png" alt="image" style="width: 400px;"></td>
  </tr>
  
   <tr style="
    background-color: #3086c5;
">
    <td align="center" style="
    padding: 14px 15px;
    text-align: center;
    color: #fff;
    font-size: 14px;
">View the full <a href="<?php echo site_url('terms-and-condition'); ?>" style="color:#fff;" target="_blank"> <strong style="font-weight: 600;">Terms and Conditions</strong></a></td>
  </tr>
     </table>
              

        
        <!--[if gte mso 9]>
           </td>
         </tr>
        </table>
        </center>
     <![endif]--></td>
    </tr>
  </table>
</center>
</body>
</html>
