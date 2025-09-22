function closeLoginModal(event) {
	if (
		event.target === event.currentTarget ||
		event.target.classList.contains("close-btn")
	) {
		const modal = document.getElementById("loginModal");
		modal.classList.remove("show");
		document.body.style.overflow = "auto";

		// Xoá tham số login=1 khỏi URL
		if (window.location.search.includes("login=1")) {
			const newUrl = window.location.origin + window.location.pathname;
			history.replaceState({}, document.title, newUrl);
		}
	}
}

document.addEventListener("DOMContentLoaded", function () {
	if (window.location.search.includes("login=1")) {
		const modal = document.getElementById("loginModal");
		if (modal) {
			modal.classList.add("show");
			document.body.style.overflow = "hidden";
		}
	}
});
