// This script finds the path to the Chromium executable bundled with puppeteer.
const puppeteer = require('puppeteer');

// puppeteer.executablePath() returns the path to the bundled Chromium.
// We just print it to stdout so the PHP script can capture it.
console.log(puppeteer.executablePath());
