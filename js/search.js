document.addEventListener("DOMContentLoaded", function () {
  const bar = document.getElementById("search-bar");
  const toggleBtn = document.getElementById("search-toggle"); // nút mở
  const closeBtn = document.getElementById("close-search");   // nút đóng (X)
  const input = document.getElementById("search-input");
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
        if (!data || data.length === 0) {
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
                  <small>${item.description?.substring(0, 60) || ""}</small>
                </a>
              </li>
            `
          )
          .join("");
        results.classList.add("show");
      })
      .catch(() => {
        results.innerHTML = "<li>Lỗi khi tìm kiếm.</li>";
        results.classList.add("show");
      });
  }

  // Debounce input
  let timeout;
  input.addEventListener("input", function () {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
      if (input.value.trim().length > 0) {
        searchNovels();
      } else {
        results.innerHTML = "";
        results.classList.remove("show");
      }
    }, 300);
  });

  // Enter để tìm + ESC để thoát
  input.addEventListener("keydown", function (e) {
    if (e.key === "Enter") searchNovels();
    if (e.key === "Escape") closeOverlay();
  });

  // Mở overlay (dùng body.search-active thay vì #search-overlay)
  toggleBtn.addEventListener("click", function (e) {
    e.stopPropagation();
    bar.classList.add("show");
    document.body.classList.add("search-active"); // thêm lớp để bật overlay mờ
    setTimeout(() => input.focus(), 200);
  });

  // Đóng overlay
  function closeOverlay() {
    bar.classList.remove("show");
    document.body.classList.remove("search-active");
    results.classList.remove("show");
    input.value = "";
    input.blur();
  }

  closeBtn.addEventListener("click", closeOverlay);
/*
  // Click ngoài => đóng
  document.addEventListener("click", function (e) {
    if (document.body.classList.contains("search-active") && !bar.contains(e.target) && !toggleBtn.contains(e.target)) {
      closeOverlay();
    }
  });
*/
  // Click kết quả => đóng
  results.addEventListener("click", function (e) {
    if (e.target.closest("a")) {
      closeOverlay();
    }
  });
});
