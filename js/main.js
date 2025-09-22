function showMore(button) {
	const mainSection = button.previousElementSibling;

	if (mainSection.classList.contains("expanded")) {
		mainSection.classList.remove("expanded");
		button.textContent = "XEM THÊM";
	} else {
		mainSection.classList.add("expanded");
		button.textContent = "THU GỌN";
	}
}
