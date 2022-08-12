<template>
  <v-container fluid class="pa-3">
    <v-row class="rates mb-8 align-center">
      <v-col cols="9">
        <v-container fluid grid-list-lg v-show="loading">
          <v-progress-circular indeterminate v-bind:size="70" color="primary" v-show="loading"></v-progress-circular>
        </v-container>
        <line-chart
            :height="200"
            :datasets="datasets"
            :labels="labels()"
            v-show="!loading"
        ></line-chart>
      </v-col>
      <v-col cols="2">
        <h2>{{ currentRate() }}</h2>
      </v-col>
    </v-row>
    <v-row v-for="(item, key) in items" :key="key">
      <template v-if="team + 1 === item.team_id">
        <v-col cols="3" class="pl-13">
          <h2 class="mb-3 ml-2">{{ item.name }} {{ item.rate }}</h2>
          <bar-chart
              :height="150"
              :datasets="userDatasets[item.user_id]"
              :labels="labels(false)"
          ></bar-chart>
        </v-col>
        <v-col cols="8">
          <h3>План</h3>
          <p>{{ item.plan }}</p>
          <h3>Результат</h3>
          <p>{{ item.result }}</p>
          <h3>Следующая неделя</h3>
          <p>{{ item.next_week }}</p>
        </v-col>
      </template>
    </v-row>
    <h3 v-if="!items">Отчётов за данную неделю не найдено</h3>
  </v-container>
</template>

<script>

import LineChart from './base/LineChart';
import BarChart from './base/BarChart';
import axios from "axios";

export default {
  components: {LineChart, BarChart},
  props: {
    items: {
      type: Array
    },
    week: {
      type: Number
    },
    team: {
      type: Number
    }
  },
  watch: {
    items: function (items) {
      this.loadRates(items)
    }
  },
  data () {
    return {
      loading: true,
      datasets: [],
      userDatasets: []
    }
  },
  mounted() {
    this.loadRates(this.items)

    return axios.get('/averageRates')
      .then(res => {
        this.datasets = [{
          borderColor: '#e9300f',
          backgroundColor: 'rgb(25 118 210 / 30%)',
          pointBackgroundColor: '#e9300f',
          data: res.data.rates
        }]
        this.loading = false
      })
  },
  methods: {
    weekDates(weekNumber) {
      let week = this.$moment(this.$moment().year().toString()).add(weekNumber, 'weeks');
      let weekStart = week.startOf('isoWeek').format('DD.MM');
      let weekEnd = week.endOf('isoWeek').format('DD.MM');

      return weekStart + ' - ' + weekEnd;
    },
    labels(full = true) {
      let labels = []
      for (let i = this.week - 7; i <= this.week; i++) {
        if (full) {
          labels.push(i + ' (' + this.weekDates(i) + ')')
        } else {
          labels.push(i)
        }
      }
      return labels
    },
    currentRate() {
      let sum = 0;
      if (this.items) {
        for (let item of this.items) {
          sum += item.rate;
        }
        return (sum / this.items.length).toFixed(1);
      } else {
        return 0;
      }
    },
    loadRates(items) {
      for (let key in items) {
        let user_id = items[key].user_id
        axios.get('/usersRates/' + user_id + '/' + this.week)
          .then(res => {
            this.userDatasets[user_id] = [{
              borderColor: '#e9300f',
              backgroundColor: 'rgb(25 118 210 / 30%)',
              pointBackgroundColor: '#e9300f',
              data: res.data
            }]
            this.$forceUpdate()
          })
      }
    }
  }
}
</script>

<style>
  p {
    white-space: pre;
  }
  .rates h2 {
    font-size: 50px;
  }
</style>