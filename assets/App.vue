<template>
  <v-app>
    <v-app-bar
      app
      color="primary"
      dark
    >
      <div class="d-flex align-center">
        <router-link to="/">
          <v-img
              alt="Deliverest Logo"
              class="shrink mr-2"
              contain
              src="../logo.png"
              transition="scale-transition"
              width="40"
          />
        </router-link>
      </div>
      <v-tabs
          v-if="currentComponent.component === 'Productivity'"
          v-model="tab"
          align-with-title
      >
        <v-tabs-slider color="yellow"></v-tabs-slider>

        <v-tab>Управление</v-tab>
        <v-tab>Маркетинг</v-tab>
        <v-tab>Команда 3</v-tab>
      </v-tabs>
      <v-spacer></v-spacer>
      <v-dialog
          v-model="dialog"
          width="500"
      >
        <template v-slot:activator="{ on, attrs }">
          <v-btn color="red lighten-2" dark v-bind="attrs" v-on="on">Добавить</v-btn>
        </template>

        <v-card>
          <v-card-text>
            <v-text-field type="text" v-model="password" label="Введите ваш ключ"></v-text-field>
          </v-card-text>

          <v-divider></v-divider>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="secondary" text @click="dialog = false">Отмена</v-btn>
            <v-btn color="primary" text @click="$router.push('/add?key=' + password); dialog = false;">Добавить</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-app-bar>

    <v-main>
      <v-row>
        <v-col cols="2">
          <v-navigation-drawer permanent>
            <v-list nav>
              <v-list-item>
                <v-btn v-if="showAllWeeks" @click="showAllWeeks = false">Скрыть все</v-btn>
                <v-btn v-else @click="showAllWeeks = true">Показать все</v-btn>
              </v-list-item>
              <v-list-item-group
                  v-if="showAllWeeks"
                  v-model="currentWeek"
                  color="primary"
              >
                <v-list-item
                    v-for="n in 53"
                    :key="n"
                    :value="n"
                    link
                >
                  <v-list-item-content>
                    <v-list-item-title>{{ n }} ({{ weekDates(n) }})</v-list-item-title>
                  </v-list-item-content>
                </v-list-item>
              </v-list-item-group>
              <v-list-item-group
                  v-else
                  v-model="currentWeek"
                  color="primary"
              >
                <v-list-item
                    v-for="n in smallWeek"
                    v-if="n > smallWeek - 4"
                    :key="n"
                    :value="n"
                    link
                >
                  <v-list-item-content>
                    <v-list-item-title>{{ n }} ({{ weekDates(n) }})</v-list-item-title>
                  </v-list-item-content>
                </v-list-item>
              </v-list-item-group>
            </v-list>
          </v-navigation-drawer>
        </v-col>
        <v-col cols="10">
          <component :items="items[currentWeek]" :week="currentWeek" :team="tab" :is="component"></component>
        </v-col>
      </v-row>
    </v-main>
  </v-app>
</template>

<script>
import axios from 'axios';

export default {
  data () {
    return {
      currentWeek: null,
      smallWeek: null,
      activeWeek: null,
      showAllWeeks: false,
      currentComponent: {},
      tab: null,
      password: null,
      dialog: false,
      items: {},
      navigation: [
        {
          title: 'Продуктивность',
          component: 'Productivity',
          path: '/'
        },
        {
          title: 'Добавить отчёт',
          component: 'Report',
          path: '/add'
        }
      ]
    }
  },
  mounted() {
    axios
        .get('/reports')
        .then(response => (this.items = response.data))

    this.currentWeek = this.$moment().isoWeek()
    this.smallWeek = this.$moment().isoWeek()

    if (localStorage.password) {
      this.password = localStorage.password;
    }
  },
  computed: {
    component () {
      if (this.currentComponent.component) {
        return () => import(`./components/${this.currentComponent.component}.vue`)
      }
    }
  },
  watch: {
    $route(to) {
      this.navigate(to.path)
    },
    password(newPassword) {
      localStorage.password = newPassword;
    }
  },
  created() {
    this.navigate(this.$route.path)
  },
  methods: {
    weekDates(weekNumber) {
      let week = this.$moment(this.$moment().year().toString()).add(weekNumber, 'weeks');
      let weekStart = week.startOf('isoWeek').format('DD.MM');
      let weekEnd = week.endOf('isoWeek').format('DD.MM');

      return weekStart + ' - ' + weekEnd;
    },
    navigate(to) {
      for (let nav in this.navigation) {
        if (this.navigation[nav].path === to) {
          this.currentComponent = this.navigation[nav]
        }
      }
    }
  }
};
</script>

<style>
  .v-navigation-drawer {
    max-height: calc(100vh - 76px);
  }
</style>