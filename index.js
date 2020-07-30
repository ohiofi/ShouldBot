const express = require('express');
const app = express();
require('dotenv').config();
const Twit = require('twit');
// const T = new Twit({
//   consumer_key:         process.env.CONSUMER_KEY,
//   consumer_secret:      process.env.CONSUMER_SECRET,
//   access_token:         process.env.ACCESS_KEY,
//   access_token_secret:  process.env.ACCESS_SECRET,
//   timeout_ms:           60*1000,  // optional HTTP request timeout to apply to all requests.
//   strictSSL:            true,     // optional - requires SSL certificates to be valid.
// });

// yr code here

app.listen(
  process.env.PORT || 3000,
  ()=>console.log("bot running")
);
