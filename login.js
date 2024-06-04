/* ========= SHOW HIDDEN - PASSWORD ======== */
const showHiddenPass = (loginPass, loginEye) => {
  const input = document.getElementById(loginPass),
    iconEye = document.getElementById(loginEye);
  iconEye.addEventListener("click", () => {
    // change password to text
    if (input.type == "password") {
      // swithc to text
      input.type = "text";

      // switch icon
      iconEye.classList.add("ri-eye-line");
      iconEye.classList.remove("ri-eye-off-line");
    } else {
      input.type = "password";

      iconEye.classList.add("ri-eye-off-line");
      iconEye.classList.remove("ri-eye-line");
    }
  });
};

showHiddenPass("login-pass", "login-eye");
