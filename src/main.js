import Vue from 'vue'
import { Button, Select, row, col, card, scrollbar, divider,select,option,tag,input,} from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import axios from 'axios'
import App from './App.vue'


Vue.prototype.$http = axios;
Vue.use(Button)
Vue.use(Select)
Vue.use(row)
Vue.use(col)
Vue.use(card)
Vue.use(scrollbar)
Vue.use(divider)
Vue.use(select)
Vue.use(option)
Vue.use(tag)
Vue.use(input)
Vue.config.productionTip = false

new Vue({
  render: h => h(App),
}).$mount('#app')
