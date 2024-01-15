<?php

  # This script updates Dynamic DNS records on DNS Made Easy's
  # DNS servers.  You must have php installed on your webserver
  # for this script to work.
  #
  # As each domain is updated the response from DNS Made Easy is
  # returned for each modification.
  # At the time this script was written DNS Made EAsy returns the following.
  #
  # Success - When a successful update is made.
  # error-record-ip-same - When the ip being updated is the same as the current record.
  #
  #
  # Author: Cameron Just <dnsmadeeasy@phoenixdigital.com>
  # Last Modified: 26-September-2002
  #
  # This script is released as public domain in hope that it will
  # be useful to others using DNS Made Easy.  It is provided
  # as-is with no warranty implied.  Sending passwords as a part 
  # of an HTTP request is inherently insecure.  I take no responsibility
  # if your password is discovered by use of this script.
  #

  # THIS IS AN ASSOCIATIVE ARRAY OF YOUR DOMAINS HELD ON DNSMADEEASY
  # The number is the id of the A Record which can be found by clicking on 
  # the DDNS link in your DNS Made Easy admin screen.
  $aRecordIDs = array("shop.kokua.co.uk" =>"64405500");

// create a new cURL resource
$ch = curl_init();

// set URL and other appropriate options
curl_setopt($ch, CURLOPT_URL, "http://myip.dnsmadeeasy.com");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_HEADER, 0);

// grab URL and pass it to the browser
$result=curl_exec($ch);
echo $result;


  $strNewIpAddress =trim($result);
  $strUsername = "candcr";
  $strPassword = "butt0n5!";

  while (list ($key, $val) = each ($aRecordIDs))
  {

    echo "Processing '$key' ............";
    $contents = readfile('https://ipv4.cloudns.net/api/dynamicURL/?q=MjcyNzE5ODoyMDAyMDk4NTk6MDk4NDJmNDliZGFjZGI1MmFjZDBlZjk5NzkyYjJiYjYxNzNhMWMwYzJhMDk1ZTA4OGJkMzQ4ZjM2YmMxZWNlMg');
    echo trim($contents);
   echo "\n";
    flush();

  }
  
?>
