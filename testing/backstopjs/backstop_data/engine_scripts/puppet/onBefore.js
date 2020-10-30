/**
 * @file
 */

module.exports = async(page, scenario, vp) => {
  await require('./loadCookies')(page, scenario);
  if (scenario.disableJavascript) {
    await page.setJavaScriptEnabled(false);
  }
};
