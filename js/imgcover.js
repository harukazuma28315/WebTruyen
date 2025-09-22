const coverInput = document.querySelector('input[name="cover"]');
const previewImg = document.createElement("img");
previewImg.style.maxWidth = "150px";
previewImg.style.marginTop = "10px";
coverInput.addEventListener("change", function () {
	const file = this.files[0];
	if (file && file.type.startsWith("image/")) {
		const reader = new FileReader();
		reader.onload = function (e) {
			previewImg.src = e.target.result;
			coverInput.parentNode.insertBefore(previewImg, coverInput.nextSibling);
		};
		reader.readAsDataURL(file);
	}
});
