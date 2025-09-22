document.addEventListener("DOMContentLoaded", function () {
	// Mở login modal
	window.openLoginModal = function () {
		document.getElementById("loginModal").classList.add("show");
		document.body.style.overflow = "hidden";
	};

	// Đóng register modal
	window.closeRegisterModal = function (event) {
		if (event && event.target !== event.currentTarget) return;
		document.getElementById("registerModal").classList.remove("show");
		document.body.style.overflow = "auto";
	};

	// Chuyển sang modal đăng ký
	var openReg = document.getElementById("openRegister");
	if (openReg) {
		openReg.addEventListener("click", function (e) {
			e.preventDefault();
			document.getElementById("loginModal").classList.remove("show");
			document.getElementById("registerModal").classList.add("show");
			document.body.style.overflow = "hidden";
		});
	}
	// Quay lại modal đăng nhập
	var backLogin = document.getElementById("backToLogin");
	if (backLogin) {
		backLogin.addEventListener("click", function (e) {
			e.preventDefault();
			document.getElementById("registerModal").classList.remove("show");
			document.getElementById("loginModal").classList.add("show");
			document.body.style.overflow = "hidden";
		});
	}
	// Đăng nhập mạng xã hội
	document.querySelectorAll(".social-btn-circle").forEach((btn) => {
		btn.addEventListener("click", function () {
			alert("Tùy chọn đăng nhập mạng xã hội!");
		});
	});
});
//nay la dang nhap
document.addEventListener("DOMContentLoaded", function () {
	const params = new URLSearchParams(window.location.search);
	if (params.get("login") === "1") {
		document.getElementById("loginModal").classList.add("show");
		document.body.style.overflow = "hidden";
	}
});
