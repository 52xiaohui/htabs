<template>
	<div id="app">
	  <el-row type="flex" justify="center">
		<el-col :span="8">
		  <el-button plain>{{ weatherInfo }}</el-button>
		</el-col>
		<el-col :span="8" style="text-align: center">
		  <el-tag type="success">
			<div style="font-size: 25px">{{ formattedDate }}</div>
		  </el-tag>
		</el-col>
		<el-col :span="8"></el-col>
	  </el-row>
	  <div class="container">
		<el-select
		  v-model="EngineValue"
		  placeholder="请选择"
		  class="select"
		  style="width: 80px"
		  @change="currStationChange"
		>
		  <el-option
			v-for="item in EngineOptions"
			:key="item.EngineValue"
			:label="item.name"
			:value="item.EngineValue"
		  >
			<span>{{ item.name }}</span>
		  </el-option>
		</el-select>
		<el-input
		  v-model="SearchKey"
		  @keyup.enter.native="go()"
		  ref="Focusing"
		  :placeholder="hitokoto"
		  style="width: 60vh"
		>
		  <i slot="suffix" class="el-input__icon el-icon-search" @click="go()"></i>
		</el-input>
	  </div>
  
	  <div style="margin-top: 15vh"></div>
	  <el-row :gutter="18">
		<el-col :md="6" :sm="12" v-for="category in newsCategories" :key="category.id">
		  <el-card style="box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1)">
			<div slot="header">
			  <span>{{ category.name }}</span>
			</div>
			<div style="height: 49vh">
			  <el-scrollbar style="height: 100%">
				<div v-for="(item, index) in category.data" :key="index">
				  <a :href="item.url" target="_blank" style="display: inline">{{index + 1}}. {{ item.title }}</a>
				  <el-divider></el-divider>
				</div>
			  </el-scrollbar>
			</div>
		  </el-card>
		</el-col>
	  </el-row>
	  <div class="footer">
		<p>Copyright © 2022 梨清回</p>
	  </div>
	</div>
  </template>
  
  <script>
  export default {
	name: 'App',
	data() {
	  return {
		EngineOptions: [
		  { EngineValue: 'https://www.baidu.com/s?wd=', name: '百度' },
		  { EngineValue: 'https://www.google.com/search?q=', name: '谷歌' },
		  { EngineValue: 'https://cn.bing.com/search?q=', name: '必应' },
		  { EngineValue: 'https://zh.wikipedia.org/wiki/', name: '维基百科' },
		  { EngineValue: 'https://duckduckgo.com/?q=', name: 'DuckDuckGo' },
		],
		EngineValue: 'https://cn.bing.com/search?q=',
		SearchKey: '',
		newsCategories: [
		  { id: 'baidu', name: '百度热搜', data: [] },
		  { id: 'bilibili', name: 'BiliBili', data: [] },
		  { id: 'toutiao', name: '今日头条', data: [] },
		  { id: 'txnews', name: '腾讯新闻', data: [] },
		],
		WeatherData: '',
		hitokoto: '',
	  };
	},
	computed: {
	  formattedDate() {
		return new Date().toLocaleString();
	  },
	  weatherInfo() {
		const { city, weather, temperature } = this.WeatherData;
		return `${city} - ${weather} - ${temperature}℃`;
	  },
	},
	created() {
	  if (localStorage.getItem('SearchUrl') !== null) {
		this.EngineValue = window.localStorage.getItem('SearchUrl');
	  }
	},
	methods: {
	  currStationChange(val) {
		window.localStorage.setItem('SearchUrl', val);
	  },
	  go() {
		const substance = this.EngineValue + this.SearchKey;
		window.open(substance);
	  },
	},
	mounted() {
	  const baseUrl = 'https://ash345075666518.icode.run/api/';
	  const getNewsData = async (id) => {
		const response = await fetch(baseUrl + 'News?source=' + id);
		const data = await response.json();
		return data;
	  };
  
	  const getWeatherData = async () => {
		const response = await fetch(baseUrl + 'Weather');
		const data = await response.json();
		return data;
	  };
  
	  const getHitokoto = async () => {
		const response = await fetch(baseUrl + 'Hitokoto');
		const data = await response.json();
		return data;
	  };
  
	  this.newsCategories.forEach(async (category) => {
		const newsData = await getNewsData(category.id);
		category.data = newsData;
	  });
  
	  this.WeatherData = await getWeatherData();
	  this.hitokoto = await getHitokoto();
  
	  this.$nextTick(() => {
		this.$refs.Focusing.focus();
	  });
	},
  };
  </script>
  
  <style scoped>
  .container {
	display: flex;
	justify-content: center;
	align-items: center;
	margin-top: 10vh;
  }
  
  .footer {
	text-align: center;
	margin-top: 30px;
  }
  </style>