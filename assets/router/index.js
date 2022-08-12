import Vue from 'vue'
import VueRouter from 'vue-router'
import Productivity from '../components/Productivity.vue'
import Report from '../components/Report.vue'

const routes = [
  {
    path: '/',
    name: 'Productivity',
    component: Productivity
  },
  {
    path: '/add',
    name: 'Report',
    component: Report
  }
]

Vue.use(VueRouter);

export default new VueRouter({
  mode: 'history',
  routes: routes
});