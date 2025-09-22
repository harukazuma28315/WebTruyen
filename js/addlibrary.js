function thaydoiLB(btn) {
	const novelId = btn.getAttribute("data-novel-id");

	fetch("changebuttonLB.php", {
		method: "POST",
		headers: {
			"Content-Type": "application/x-www-form-urlencoded",
		},
		body: "novel_id=" + encodeURIComponent(novelId),
	})
		.then((res) => res.json())
		.then((data) => {
			if (data.success) {
				btn.textContent = data.in_library ? "ĐÃ THÊM" : "THÊM VÀO THƯ VIỆN";
			} else {
				alert(data.message);
			}
		})
		.catch((err) => {
			console.error("AJAX error:", err);
			alert("Đã xảy ra lỗi.");
		});
}
