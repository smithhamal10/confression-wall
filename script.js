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
  let confessions = []; // This will hold confession objects locally for demo

  // Sample initial confessions (would come from server)
  confessions = [
    {
      id: 1,
      message: "I wish I told her how I felt.",
      timestamp: new Date(Date.now() - 3600000),
      mood: "😢",
      reactions: { love: 5, funny: 1, sad: 4, bold: 0 },
    },
    {
      id: 2,
      message: "I secretly enjoy my work-from-home days.",
      timestamp: new Date(Date.now() - 7200000),
      mood: "😎",
      reactions: { love: 3, funny: 6, sad: 0, bold: 2 },
    },
    {
      id: 3,
      message: "I want to apologize but I’m too scared.",
      timestamp: new Date(Date.now() - 1800000),
      mood: "🥺",
      reactions: { love: 4, funny: 0, sad: 3, bold: 1 },
    },
  ];

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

  // Render one confession block
  function renderConfession(conf) {
    const div = document.createElement("div");
    div.className = "confession";

    // Message + mood
    div.innerHTML = `
      <p>${conf.message}</p>
      <small>Mood: ${conf.mood || "😶"} — ${formatTime(conf.timestamp)}</small>
      <div class="reactions" data-id="${conf.id}">
        <button class="reaction-btn" data-reaction="love">❤️ ${conf.reactions.love}</button>
        <button class="reaction-btn" data-reaction="funny">😂 ${conf.reactions.funny}</button>
        <button class="reaction-btn" data-reaction="sad">😢 ${conf.reactions.sad}</button>
        <button class="reaction-btn" data-reaction="bold">🔥 ${conf.reactions.bold}</button>
      </div>
    `;

    // Reaction buttons click handler
    const buttons = div.querySelectorAll(".reaction-btn");
    buttons.forEach((btn) => {
      btn.addEventListener("click", () => {
        const reaction = btn.dataset.reaction;
        // Toggle reaction count locally (demo)
        conf.reactions[reaction]++;
        btn.textContent = `${btn.textContent.split(" ")[0]} ${conf.reactions[reaction]}`;
      });
    });

    return div;
  }

  // Render all confessions to container
  function renderConfessions(list) {
    confessionsContainer.innerHTML = "";
    if (list.length === 0) {
      confessionsContainer.innerHTML = "<p>No confessions found.</p>";
      return;
    }
    list.forEach((c) => {
      confessionsContainer.appendChild(renderConfession(c));
    });
  }

  // Render trending confessions (top 3 by total reactions)
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
    trending.forEach((c) => {
      const el = document.createElement("div");
      el.className = "confession";
      el.innerHTML = `<p>${c.message}</p><small>${formatTime(c.timestamp)}</small>`;
      trendingContainer.appendChild(el);
    });
  }

  // Update character count on textarea
  messageInput.addEventListener("input", () => {
    charCount.textContent = `${messageInput.value.length} / 1000`;
  });

  // Mood selector buttons
  moodButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      moodButtons.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      currentMood = btn.textContent;
    });
  });

  // Form submit handler (demo: add confession locally)
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const message = messageInput.value.trim();
    if (!message) {
      alert("Please write something before submitting.");
      return;
    }
    if (!currentMood) {
      alert("Please select a mood before submitting.");
      return;
    }

    const newConfession = {
      id: confessions.length + 1,
      message,
      timestamp: new Date(),
      mood: currentMood,
      reactions: { love: 0, funny: 0, sad: 0, bold: 0 },
    };
    confessions.unshift(newConfession); // newest on top
    renderConfessions(filterAndSort(confessions));
    renderTrending();

    form.reset();
    charCount.textContent = "0 / 1000";
    currentMood = null;
    moodButtons.forEach((b) => b.classList.remove("active"));
    alert("Thank you for your confession!");
  });

  // Search + Sort functionality
  function filterAndSort(list) {
    const searchTerm = searchInput.value.toLowerCase();
    let filtered = list.filter((c) => c.message.toLowerCase().includes(searchTerm));

    const sortType = sortSelect.value;
    if (sortType === "latest") {
      filtered.sort((a, b) => b.timestamp - a.timestamp);
    } else if (sortType === "popular") {
      filtered.sort(
        (a, b) =>
          Object.values(b.reactions).reduce((x, y) => x + y, 0) -
          Object.values(a.reactions).reduce((x, y) => x + y, 0)
      );
    } else if (sortType === "random") {
      for (let i = filtered.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [filtered[i], filtered[j]] = [filtered[j], filtered[i]];
      }
    }
    return filtered;
  }

  // On search or sort change
  searchInput.addEventListener("input", () => {
    renderConfessions(filterAndSort(confessions));
  });
  sortSelect.addEventListener("change", () => {
    renderConfessions(filterAndSort(confessions));
  });

  // INITIAL render
  renderConfessions(confessions);
  renderTrending();
});
