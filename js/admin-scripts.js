function copyToClipboard() {
  var copyText = document.getElementById("ppsl_secure_link");
  copyText.select();
  document.execCommand("copy");
}
