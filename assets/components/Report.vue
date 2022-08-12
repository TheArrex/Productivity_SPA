<template>
  <v-container v-if="Object.keys(report).length !== 0" fluid class="pa-3 mt-8">
    <v-row class="align-center">
      <v-col cols="1">
        <v-text-field
            v-model.number="report.rate"
            append-outer-icon="add"
            @click:append-outer="increment"
            prepend-icon="remove"
            @click:prepend="decrement"
        ></v-text-field>
      </v-col>
      <v-col cols="7">
        <v-textarea
            label="Результат"
            v-model="report.result"
        ></v-textarea>
        <v-textarea
            label="План на следующую неделю"
            v-model="report.next_week"
        ></v-textarea>
      </v-col>
    </v-row>
    <v-checkbox color="red lighten-2" v-model="report.is_published" label="Опубликовать"></v-checkbox>
    <v-btn color="secondary" @click="saveReport">Сохранить</v-btn>
  </v-container>
  <h3 v-else class="pa-3 mt-8">Пользователя с таким ключом не существует</h3>
</template>

<script>
import axios from "axios";

export default {
  data () {
    return {
      report: {}
    }
  },
  mounted() {
    axios
        .get('/report', {params: {key: this.$route.query.key}})
        .then(response => {
          this.report = response.data
        })
  },
  methods: {
    increment () {
      let value = parseFloat(this.report.rate) + 0.5
      if (value <= 10) {
        this.report.rate = value
      }
    },
    decrement () {
      let value = parseFloat(this.report.rate) - 0.5
      if (value >= 0) {
        this.report.rate = value
      }
    },
    saveReport() {
      axios
        .post('/saveReport', this.report)
        .then(function (response) {
          if (!response.data.success) {
            alert(response.data.error)
          } else {
            alert('Отчёт сохранён')
            window.location.href = '/'
          }
        });
    }
  }
}
</script>

<style>
  .container .row {
    gap: 50px;
  }

  .v-text-field input {
    font-size: 30px;
  }

  .v-input--selection-controls__input input[role=checkbox] {
    opacity: 1;
  }
</style>