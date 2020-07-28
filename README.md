# whmcs-hooks-whatsapp

https://indrahartawan.com

WHMCS hook to Whatsapp. Send notification to whatsapp or user on ticket open and user reply.

# Installation

Just copy whatsapp.php and whatsapp.json to $WHMCS_ROOT/includes/hooks directory.

# Configuration 

Edit file `whatsapp.json` and change `whatsapp_url` to your preferred WhatsApp API like Woo-Wa (http://api.woo-wa.com/). `whatsapp_api_userkey` and `whatsapp_api_passkey` in when you are using Zenziva.id service.

```
 {
    "whatsapp_api_url"     : "your_whatsapp_api_url", 
    "whatsapp_api_userkey" : "your_whatsapp_api_userkey",
    "whatsapp_api_passkey" : "your_whatsapp_api_passkey",
    "whatsapp_api_key"     : "your_whatsapp_api_key_or_token", 
    "whatsapp_api_license" : "your_whatsapp_api_license", 
    "adminuser" : "your_whmcs_admin_username",
    "debug"     : true
 }
```

Explanation

`whatsapp_api_url`: your whatsapp API provider's URL

`whatsapp_api_userkey`: your whatsapp API provider's USERKEY

`whatsapp_api_passkey`: your whatsapp API provider's PASSKEY

`whatsapp_api_key`: your whatsapp API provider's KEY or TOKEN

`whatsapp_api_license`: your whatsapp API provider's license

`adminuser`: WHMCS admin username to call WHMCS API admin function

`debug` : `true` to enable debug to whatsapp.log file, and `false` to disabled.

# Done
Now, try create an new order after you login to client area. You should receive notification from WHMCS every time new order created.
