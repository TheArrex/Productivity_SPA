<script>
  import { Bar } from 'vue-chartjs'
  export default {
    extends: Bar,
    props: {
      datasets: {
        type: Array,
        default: null
      },
      labels: {
        type: Array,
        default: null
      }
    },
    watch: {
      datasets: function (datasets) {
        this.renderChart({labels: this.labels, datasets: datasets}, this.options)
      }
    },
    data() {
      return {
        options: {
          responsive: true,
          maintainAspectRatio: false,
          legend: {
            display: false
          },
          scales: {
            yAxes: [{
              display: true,
              ticks: {
                beginAtZero: true,
                max: 10
              }
            }]
          },
        }
      }
    }
  }
</script>