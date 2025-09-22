document.addEventListener("DOMContentLoaded", function () {
	// Mở modal khi bấm nút CREATE NEW
	document.getElementById("openCreateNovelModal").onclick = function (e) {
		e.preventDefault();
		document.getElementById("createNovelModal").style.display = "flex";
		document.body.style.overflow = "hidden";
	};

	// Đóng modal
	function closeCreateNovelModal() {
		document.getElementById("createNovelModal").style.display = "none";
		document.body.style.overflow = "auto";
	}

	// Đóng modal khi bấm nền ngoài
	document.getElementById("createNovelModal").onclick = function (e) {
		if (e.target === this) closeCreateNovelModal();
	};

	function toggleTagList(label) {
		const dropdown = label.parentElement;
		const list = dropdown.querySelector(".dropdown-tag-list");
		label.classList.toggle("active");
		list.classList.toggle("show");
	}

	// Đóng dropdown khi click ra ngoài
	document.addEventListener("click", function (e) {
		document.querySelectorAll(".dropdown-tag").forEach(function (dropdown) {
			if (!dropdown.contains(e.target)) {
				dropdown.querySelector(".dropdown-tag-list").classList.remove("show");
				dropdown
					.querySelector(".dropdown-tag-label")
					.classList.remove("active");
			}
		});
	});

	// Update label khi chọn tag
	document.querySelectorAll(".dropdown-tag").forEach(function (dropdown) {
		const label = dropdown.querySelector(".dropdown-tag-label");
		const checkboxes = dropdown.querySelectorAll(
			'.dropdown-tag-list input[type="checkbox"]'
		);

		checkboxes.forEach(function (cb) {
			cb.addEventListener("change", function () {
				const checked = dropdown.querySelectorAll(
					'.dropdown-tag-list input[type="checkbox"]:checked'
				);
				if (checked.length > 0) {
					let selected = [];
					checked.forEach(function (item) {
						// Lấy text sau input (label text)
						selected.push(item.parentNode.textContent.trim());
					});
					label.innerHTML =
						selected.join(", ") +
						' <i class="fa fa-caret-down" style="float:right;"></i>';
				} else {
					label.innerHTML =
						'Tag <i class="fa fa-caret-down" style="float:right;"></i>';
				}
			});
		});
	});

	// Bật/tắt dropdown menu user (avatar)
	document
		.querySelector(".dropdown-btn")
		.addEventListener("click", function (e) {
			e.stopPropagation(); // Ngăn sự kiện nổi lên document
			this.parentElement.classList.toggle("open");
		});

	// Đóng dropdown khi click ra ngoài
	document.addEventListener("click", function () {
		document.querySelectorAll(".dropdown").forEach(function (dropdown) {
			dropdown.classList.remove("open");
		});
	});
});
