document.addEventListener("DOMContentLoaded", function () {
	const input = document.querySelector(".search-bar input");
	const btn = document.querySelector(".search-bar button");
	const results = document.getElementById("search-results");

	// Hàm gọi API tìm kiếm
	function searchNovels() {
		const q = input.value.trim();
		if (!q) {
			results.innerHTML = "";
			results.classList.remove("show");
			return;
		}
		fetch("../novels/search.php?q=" + encodeURIComponent(q))
			.then((res) => res.json())
			.then((data) => {
				if (data.length === 0) {
					results.innerHTML = "<li>Không tìm thấy kết quả.</li>";
					results.classList.add("show");
					return;
				}
				results.innerHTML = data
					.map(
						(item) => `
                    <li>
                        <a href="../novels/thongtin.php?id=${item.novel_id}">
                            <b>${item.title}</b><br>
                            <small>${
															item.description?.substring(0, 60) || ""
														}</small>
                        </a>
                    </li>
                `
					)
					.join("");
				results.classList.add("show");
			});
	}

	// Sự kiện: gõ chữ tự động gợi ý
	let timeout;
	input.addEventListener("input", function () {
		clearTimeout(timeout);
		timeout = setTimeout(function () {
			const q = input.value.trim();
			// Tìm kiếm ngay khi có ít nhất 1 ký tự
			if (q.length > 0) {
				searchNovels();
			} else {
				results.innerHTML = ""; // Nếu không có gì nhập, xóa kết quả
				results.classList.remove("show");
			}
		}, 300); // 300ms delay
	});

	// Sự kiện: nhấn Enter để tìm
	input.addEventListener("keydown", function (e) {
		if (e.key === "Enter") {
			searchNovels();
		}
	});

	// Sự kiện: click nút search
	btn.addEventListener("click", function () {
		searchNovels();
	});

	// Ẩn dropdown khi click ra ngoài
	document.addEventListener("click", function (e) {
		if (!document.querySelector(".search-bar").contains(e.target)) {
			results.classList.remove("show");
		}
	});

	// Optional: Ẩn dropdown khi bấm vào 1 dòng kết quả
	results.addEventListener("click", function (e) {
		if (e.target.tagName === "A") {
			results.classList.remove("show");
		}
	});
});
