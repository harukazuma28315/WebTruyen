const tabLinks = document.querySelectorAll(".tab-link");
const tabContents = document.querySelectorAll(".tab-content");

tabLinks.forEach((link) => {
	link.addEventListener("click", function (e) {
		e.preventDefault();
		tabLinks.forEach((l) => l.classList.remove("active"));
		tabContents.forEach((c) => (c.style.display = "none"));

		this.classList.add("active");
		const selectedTab = this.getAttribute("data-tab");
		document.getElementById(`${selectedTab}-tab`).style.display = "block";
	});
});
