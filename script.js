document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("confessionForm");
  const messageInput = document.getElementById("message");
  const charCount = document.getElementById("charCount");
  const confessionsContainer = document.getElementById("confessions");
  const searchInput = document.getElementById("searchInput");
  const sortSelect = document.getElementById("sortSelect");
  const moodButtons = document.querySelectorAll(".mood-section button");
  const trendingContainer = document.getElementById("trendingConfessions");

  let currentMood = null;
  let confessions = [];

  // Format timestamp nicely
  function formatTime(date) {
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    if (diffMins < 1) return "Just now";
    if (diffMins < 60) return `${diffMins} min ago`;
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? "s" : ""} ago`;
    const diffDays = Math.floor(diffHours / 24);
    return `${diffDays} day${diffDays > 1 ? "s" : ""} ago`;
  }

  function renderConfession(conf) {
    const div = document.createElement("div");
    div.className = "confession";
    div.innerHTML = `
      <p>${conf.message}</p>
      <small>Mood: ${conf.mood || "😶"} — ${formatTime(new Date(conf.timestamp))}</small>
      <div class="reactions" data-id="${conf.id}">
        <button class="reaction-btn" data-reaction="love">❤️ ${conf.reactions.love}</button>
        <button class="reaction-btn" data-reaction="funny">😂 ${conf.reactions.funny}</button>
        <button class="reaction-btn" data-reaction="sad">😢 ${conf.reactions.sad}</button>
        <button class="reaction-btn" data-reaction="bold">🔥 ${conf.reactions.bold}</button>
      </div>
    `;

    // Add reaction click event listeners
    div.querySelectorAll(".reaction-btn").forEach(btn => {
      btn.addEventListener("click", () => {
        const reaction = btn.dataset.reaction;
        const confessionId = conf.id;

        fetch("submit_reaction.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ confession_id: confessionId, reaction }),
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            conf.reactions[reaction] = data.reactions[reaction];
            btn.textContent = `${btn.textContent.split(" ")[0]} ${conf.reactions[reaction]}`;
            renderTrending();
          } else {
            alert(data.error || "Failed to update reaction");
          }
        })
        .catch(() => alert("Network error while updating reaction"));
      });
    });

    return div;
  }

  function renderConfessions(list) {
    confessionsContainer.innerHTML = "";
    if (list.length === 0) {
      confessionsContainer.innerHTML = "<p>No confessions found.</p>";
      return;
    }
    list.forEach(c => confessionsContainer.appendChild(renderConfession(c)));
  }

  function renderTrending() {
    if (!trendingContainer) return;
    const trending = [...confessions]
      .sort((a, b) => {
        const aSum = Object.values(a.reactions).reduce((x, y) => x + y, 0);
        const bSum = Object.values(b.reactions).reduce((x, y) => x + y, 0);
        return bSum - aSum;
      })
      .slice(0, 3);

    trendingContainer.innerHTML = "";
    trending.forEach(c => {
      const el = document.createElement("div");
      el.className = "confession";
      el.innerHTML = `<p>${c.message}</p><small>${formatTime(new Date(c.timestamp))}</small>`;
      trendingContainer.appendChild(el);
    });
  }

  messageInput.addEventListener("input", () => {
    charCount.textContent = `${messageInput.value.length} / 1000`;
  });

  moodButtons.forEach(btn => {
    btn.addEventListener("click", () => {
      moodButtons.forEach(b => b.classList.remove("active"));
      btn.classList.add("active");
      currentMood = btn.textContent;
    });
  });

  form.addEventListener("submit", e => {
    e.preventDefault();
    const message = messageInput.value.trim();
    if (!message) return alert("Please write something before submitting.");
    if (!currentMood) return alert("Please select a mood before submitting.");

    fetch("submit_confession.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ message, mood: currentMood }),
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        confessions.unshift(data.confession);
        renderConfessions(filterAndSort(confessions));
        renderTrending();

        form.reset();
        charCount.textContent = "0 / 1000";
        currentMood = null;
        moodButtons.forEach(b => b.classList.remove("active"));
        alert("Thank you for your confession!");
      } else {
        alert(data.error || "Failed to submit confession.");
      }
    })
    .catch(() => alert("Network error while submitting confession."));
  });

  function filterAndSort(list) {
    const searchTerm = searchInput.value.toLowerCase();
    let filtered = list.filter(c => c.message.toLowerCase().includes(searchTerm));

    const sortType = sortSelect.value;
    if (sortType === "latest") {
      filtered.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
    } else if (sortType === "popular") {
      filtered.sort((a, b) => {
        const aSum = Object.values(a.reactions).reduce((x, y) => x + y, 0);
        const bSum = Object.values(b.reactions).reduce((x, y) => x + y, 0);
        return bSum - aSum;
      });
    } else if (sortType === "random") {
      for (let i = filtered.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [filtered[i], filtered[j]] = [filtered[j], filtered[i]];
      }
    }
    return filtered;
  }

  searchInput.addEventListener("input", () => renderConfessions(filterAndSort(confessions)));
  sortSelect.addEventListener("change", () => renderConfessions(filterAndSort(confessions)));

  // Initial load - fetch confessions from server
  fetch("get_confessions.php")
    .then(res => res.json())
    .then(data => {
      if (data.success && Array.isArray(data.confessions)) {
        confessions = data.confessions;
        renderConfessions(confessions);
        renderTrending();
      } else {
        alert("Failed to load confessions.");
      }
    })
    .catch(() => alert("Network error while loading confessions."));
});
