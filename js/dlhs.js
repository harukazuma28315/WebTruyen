//cần confirm trước khi xoá
// document.addEventListener("DOMContentLoaded", function () {
// 	document.querySelectorAll(".delete-history").forEach((button) => {
// 		button.addEventListener("click", function () {
// 			const novelId = this.getAttribute("data-id");
// 			if (!confirm("Xoá truyện này khỏi lịch sử?")) return;

// 			fetch("delete_history.php", {
// 				method: "POST",
// 				headers: { "Content-Type": "application/x-www-form-urlencoded" },
// 				body: "novel_id=" + encodeURIComponent(novelId),
// 			})
// 				.then((res) => res.json())
// 				.then((data) => {
// 					if (data.success) {
// 						const item = document.getElementById("history-" + novelId);
// 						if (item) item.remove();

// 						if (document.querySelectorAll(".history-item").length === 0) {
// 							document.querySelector(".empty-library").style.display = "block";
// 						}
// 					} else {
// 						alert("Lỗi xoá: " + data.message);
// 					}
// 				});
// 		});
// 	});
// });
// Xoá truyện khỏi lịch sử mà không cần confirm
document.addEventListener("DOMContentLoaded", function () {
	document.querySelectorAll(".delete-history").forEach((button) => {
		button.addEventListener("click", function () {
			const novelId = this.getAttribute("data-id");

			fetch("delete_history.php", {
				method: "POST",
				headers: { "Content-Type": "application/x-www-form-urlencoded" },
				body: "novel_id=" + encodeURIComponent(novelId),
			})
				.then((res) => res.json())
				.then((data) => {
					if (data.success) {
						const item = document.getElementById("history-" + novelId);
						if (item) item.remove();

						if (document.querySelectorAll(".history-item").length === 0) {
							document.querySelector(".empty-library").style.display = "block";
						}
					}
				});
		});
	});
});
