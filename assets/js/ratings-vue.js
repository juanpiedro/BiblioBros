const { createApp } = Vue;

createApp({
  data() {
    return {
      mentorId: window.__USER_ID__,
      ratings: [],
      avgScore: 0,
      loading: false,
      error: null
    };
  },
  mounted() {
    this.fetchRatings();
  },
  methods: {
    fetchRatings() {
      this.loading = true;
      fetch(`php/load_ratings.php`, {
        credentials: 'include'
      })
      .then(r => r.ok ? r.json() : Promise.reject(r.status))
      .then(data => {
        this.ratings = data;
        // Calcula la media
        const sum = data.reduce((acc, r) => acc + r.score, 0);
        this.avgScore = data.length ? (sum / data.length) : 0;
      })
      .catch(e => {
        this.error = e;
      })
      .finally(() => {
        this.loading = false;
      });
    }
  }
}).mount('#ratings-app');
