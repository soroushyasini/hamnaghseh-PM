function openEditModal(id, name, description, type) {
  document.getElementById("editModal").classList.remove("hidden");
  document.getElementById("edit_project_id").value = id;
  document.getElementById("edit_name").value = name;
  document.getElementById("edit_description").value = description;
  document.getElementById("edit_type").value = type;
}

function closeEditModal() {
  document.getElementById("editModal").classList.add("hidden");
}

 function confirmArchive() {
    return confirm("با آرشیو پروژه، دسترسی به آن دیگر ممکن نیست. آیا مطمئن هستید؟");
  }

document.getElementById("share-form").onsubmit = async (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);
  formData.append("_ajax_nonce", hamnaghsheh_ajax.nonce);
  const res = await fetch(hamnaghsheh_ajax.ajax_url, {
    method: "POST",
    body: new URLSearchParams([...formData, ["action", "create_share_link"]]),
  });
  const data = await res.json();
  if (data.success) {
    alert("✅ لینک ساخته شد: " + data.data.link);
    location.reload();
  } else {
    alert("❌ خطا: " + data.data);
  }
};



// savabegh
function translateActionType(action) {
  switch (action) {
    case "upload":
      return "آپلود اولیه";
    case "replace":
      return "جایگزینی فایل";
    case "delete":
      return "حذف فایل";
    case "download":
        return "دانلود";
    case "see" :
        return "مشاهده";
    default:
      return "نامشخص";
  }
}

function toJalali(dateString) {
  try {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat("fa-IR-u-ca-persian", {
      year: "numeric",
      month: "long",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    }).format(date);
  } catch (e) {
    console.warn("Persian date not supported, fallback to default format.");
    const date = new Date(dateString);
    return date.toLocaleString("fa-IR", {
      year: "numeric",
      month: "long",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  }
}

function openFileLogsModal(fileId) {
  const modal = document.getElementById("fileLogsModal");
  const content = document.getElementById("fileLogsContent");

  modal.classList.remove("hidden");
  modal.classList.add("flex");
  content.innerHTML =
    '<p class="text-center text-gray-400">در حال بارگذاری...</p>';

  const url =
    hamnaghsheh_ajax.ajax_url +
    "?action=get_file_logs" +
    "&file_id=" +
    encodeURIComponent(fileId) +
    "&_ajax_nonce=" +
    encodeURIComponent(hamnaghsheh_ajax.nonce);

  // ارسال درخواست AJAX برای گرفتن لاگ‌ها
  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.logs.length > 0) {
        content.innerHTML = data.logs
          .map(
            (log) => `
          <div class="border rounded-lg p-2 bg-gray-50">
            <p><span class="font-semibold text-[#09375B]">عملیات:</span> ${translateActionType(
              log.action_type
            )}</p>
            <p><span class="font-semibold text-[#09375B]">کاربر:</span> ${
              log.user_name
            }</p>
            <p><span class="font-semibold text-[#09375B]">تاریخ:</span> ${toJalali(
              log.created_at
            )}</p>
          </div>`
          )
          .join("");
      } else {
        content.innerHTML =
          '<p class="text-center text-gray-400">هیچ سابقه‌ای یافت نشد.</p>';
      }
    })
    .catch(() => {
      content.innerHTML =
        '<p class="text-center text-red-500">خطا در دریافت اطلاعات.</p>';
    });
}

function closeFileLogsModal() {
  const modal = document.getElementById("fileLogsModal");
  modal.classList.add("hidden");
  modal.classList.remove("flex");
}

/// replace
document.querySelectorAll(".replace-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    const fileId = btn.dataset.fileId;
    document.getElementById("replace_file_id").value = fileId;
    document.getElementById("replaceModal").classList.remove("hidden");
    document.getElementById("replaceModal").classList.add("flex");
  });
});
document.getElementById("closeModalBtn").addEventListener("click", () => {
  document.getElementById("replaceModal").classList.add("hidden");
});

/// download
function downloadProjectFiles(projectId) {
  const url =
    hamnaghsheh_ajax.ajax_url +
    "?action=download_project_files" +
    "&project_id=" +
    encodeURIComponent(projectId) +
    "&_ajax_nonce=" +
    encodeURIComponent(hamnaghsheh_ajax.nonce);

  window.location.href = url;
}

function copyToClipboard(text, btn) {
  navigator.clipboard.writeText(text).then(() => {
    const oldText = btn.textContent;
    btn.textContent = "کپی شد ✅";
    btn.classList.add("bg-green-100", "text-green-700");
    btn.classList.remove("bg-blue-100", "text-blue-700");
    setTimeout(() => {
      btn.textContent = oldText;
      btn.classList.add("bg-blue-100", "text-blue-700");
      btn.classList.remove("bg-green-100", "text-green-700");
    }, 2000);
  });
}

function logDownload(fileId, projectId) {
  fetch(hamnaghsheh_ajax.ajax_url, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams({
      action: "hamnaghsheh_log_download",
      file_id: fileId,
      project_id: projectId,
      _ajax_nonce: hamnaghsheh_ajax.nonce,
    }),
  })
  .then((r) => r.json())
  .then((data) => {
    if (!data.success) console.warn("خطا در ثبت لاگ دانلود");
  })
  .catch(() => console.warn("Ajax failed"));
}

function logSee(fileId, projectId) {
  fetch(hamnaghsheh_ajax.ajax_url, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams({
      action: "hamnaghsheh_log_see",
      file_id: fileId,
      project_id: projectId,
      _ajax_nonce: hamnaghsheh_ajax.nonce,
    }),
  })
  .then((r) => r.json())
  .then((data) => {
    if (!data.success) console.warn("خطا در ثبت لاگ دانلود");
  })
  .catch(() => console.warn("Ajax failed"));
}


document.addEventListener("DOMContentLoaded", () => {
  const fileInput = document.getElementById("hamnaghsheh-file-input");
  if (!fileInput) return;

  const uploadLabel = document.getElementById("hamnaghsheh-upload-label");
  const uploadQueue = document.getElementById("hamnaghsheh-upload-queue");
  const projectId = document.getElementById("hamnaghsheh-project-id")
    ? document.getElementById("hamnaghsheh-project-id").value
    : null;

  if (!projectId) return;

  let isUploading = false;

  function formatBytes(bytes) {
    if (bytes < 1024) return bytes + " B";
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + " KB";
    return (bytes / 1048576).toFixed(1) + " MB";
  }

  function createProgressRow(file) {
    const row = document.createElement("div");
    row.className = "border border-gray-200 rounded-xl p-3 bg-white shadow-sm";
    row.innerHTML = `
      <div class="flex justify-between items-center mb-1 text-xs text-[#09375B] font-medium">
        <span class="truncate max-w-[60%]">${file.name}</span>
        <span>${formatBytes(file.size)}</span>
      </div>
      <div class="bg-gray-200 rounded-full h-2.5 overflow-hidden">
        <div class="progress-fill bg-[#FFCF00] h-2.5 rounded-full transition-all" style="width:0%"></div>
      </div>
      <p class="status-text text-xs text-[#09375B] mt-1 text-left">۰٪</p>
    `;
    uploadQueue.appendChild(row);
    return row;
  }

  function uploadFile(file) {
    return new Promise((resolve) => {
      const row = createProgressRow(file);
      const fill = row.querySelector(".progress-fill");
      const status = row.querySelector(".status-text");

      const formData = new FormData();
      formData.append("action", "hamnaghsheh_ajax_upload_file");
      formData.append("project_id", projectId);
      formData.append("_ajax_nonce", hamnaghsheh_ajax.nonce);
      formData.append("file", file);

      const xhr = new XMLHttpRequest();

      xhr.upload.onprogress = (e) => {
        if (e.lengthComputable) {
          const pct = Math.round((e.loaded / e.total) * 100);
          fill.style.width = pct + "%";
          status.textContent = pct + "٪";
        }
      };

      xhr.onload = () => {
        try {
          const data = JSON.parse(xhr.responseText);
          if (data.success) {
            fill.style.width = "100%";
            fill.classList.replace("bg-[#FFCF00]", "bg-green-400");
            status.textContent = "✅ آپلود شد";
            status.classList.add("text-green-600");
          } else {
            fill.classList.replace("bg-[#FFCF00]", "bg-red-400");
            status.textContent =
              "❌ خطا: " + (data.data && data.data.message ? data.data.message : "خطای ناشناخته");
            status.classList.add("text-red-600");
          }
        } catch (e) {
          fill.classList.replace("bg-[#FFCF00]", "bg-red-400");
          status.textContent = "❌ خطا در پردازش پاسخ سرور";
          status.classList.add("text-red-600");
        }
        resolve();
      };

      xhr.onerror = () => {
        fill.classList.replace("bg-[#FFCF00]", "bg-red-400");
        status.textContent = "❌ خطا در اتصال به سرور";
        status.classList.add("text-red-600");
        resolve();
      };

      xhr.open("POST", hamnaghsheh_ajax.ajax_url, true);
      xhr.send(formData);
    });
  }

  async function processQueue(files) {
    if (isUploading) return;
    isUploading = true;
    uploadLabel.style.pointerEvents = "none";
    uploadLabel.classList.add("opacity-50");

    for (const file of files) {
      await uploadFile(file);
    }

    isUploading = false;
    uploadLabel.style.pointerEvents = "";
    uploadLabel.classList.remove("opacity-50");
    fileInput.value = "";

    const RELOAD_DELAY_MS = 1500;
    setTimeout(() => {
      location.reload();
    }, RELOAD_DELAY_MS);
  }

  fileInput.addEventListener("change", (e) => {
    const files = Array.from(e.target.files);
    if (files.length > 0) processQueue(files);
  });

  uploadLabel.addEventListener("dragover", (e) => {
    e.preventDefault();
    uploadLabel.classList.add("bg-[#e8f0f8]", "border-[#FFCF00]");
  });

  uploadLabel.addEventListener("dragleave", () => {
    uploadLabel.classList.remove("bg-[#e8f0f8]", "border-[#FFCF00]");
  });

  uploadLabel.addEventListener("drop", (e) => {
    e.preventDefault();
    uploadLabel.classList.remove("bg-[#e8f0f8]", "border-[#FFCF00]");
    const files = Array.from(e.dataTransfer.files);
    if (files.length > 0) processQueue(files);
  });
});

document.getElementById("open-share-popup").onclick = () => {
  document.getElementById("share-popup").classList.remove("hidden");
};
document.getElementById("close-share-popup").onclick = () => {
  document.getElementById("share-popup").classList.add("hidden");
};


///////// soroush updated 11/30/2025 ////
