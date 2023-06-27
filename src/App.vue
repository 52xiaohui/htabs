<template>
	<div id="app">
		<el-row type="flex" justify="center">
			<el-col :span="8">
				<el-button plain>
					{{ WeatherData.city }}-{{ WeatherData.weather }}-{{ WeatherData.temperature}}℃
				</el-button>
			</el-col>
			<el-col :span="8" style="text-align: center">
				<el-tag type="success">
					<div style="font-size: 25px">{{ formattedDate }}</div>
				</el-tag>
			</el-col>
			<el-col :span="8"> </el-col>
		</el-row>
		<div class="container">
			<el-select v-model="EngineValue" placeholder="请选择" class="select" style="width: 80px"
				@change="currStationChange">
				<el-option v-for="item in EngineOptions" :key="item.EngineValue" :label="item.name"
					:value="item.EngineValue">
					<span>{{ item.name }}</span>
				</el-option>
			</el-select>
			<el-input v-model="SearchKey" @keyup.enter.native="go()" ref="Focusing" :placeholder="hitokoto"
				style="width: 60vh">
				<i slot="suffix" class="el-input__icon el-icon-search" @click="go()"></i>
			</el-input>
		</div>

		<div style="margin-top: 15vh"></div>
		<el-row :gutter="18">
			<el-col :md="6" :sm="12">
				<el-card style="box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1)">
					<div slot="header">
						<span> 百度热搜</span>
					</div>
					<div style="height: 49vh">
						<el-scrollbar style="height: 100%">
							<div v-for="(item, index) in BaiduData" :key="index">
								<a :href="item.url" target="_blank" style="display: inline">{{ index + 1 }}. {{ item.title }}</a>

								<el-divider></el-divider>
							</div>
						</el-scrollbar>
					</div>
				</el-card>
			</el-col>
			<el-col :md="6" :sm="12">
				<el-card>
					<div slot="header">
						<span> 今日头条</span>
					</div>
					<div style="height: 49vh">
						<el-scrollbar style="height: 100%">
							<div v-for="(item, index) in ToutiaoData" v-bind:key="index + 1">
								<a :href="item.url" target="_blank" style="display: inline">{{ index + 1 }}. {{ item.title }}</a>

								<el-divider></el-divider>
							</div>
						</el-scrollbar>
					</div>
				</el-card>
			</el-col>
			<el-col :md="6" :sm="12">
				<el-card>
					<div slot="header">
						<span> 知乎热榜</span>
					</div>
					<div style="height: 49vh">
						<el-scrollbar style="height: 100%">
							<div v-for="(item, index) in ZhihuData" v-bind:key="index + 1">
								<a :href="item.url" target="_blank" style="display: inline">{{ index + 1 }}. {{ item.title }}</a>

								<el-divider></el-divider>
							</div>
						</el-scrollbar>
					</div>
				</el-card>
			</el-col>
			<el-col :md="6" :sm="12">
				<el-card>
					<div slot="header">
						<span> BiliBili</span>
					</div>
					<div style="height: 49vh">
						<el-scrollbar style="height: 100%">
							<div v-for="(item, index) in BilibiliData" v-bind:key="index + 1">
								<a :href="item.url" target="_blank" style="display: inline">{{ index + 1 }}. {{ item.title }}</a>

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
			EngineOptions: [{
				EngineValue: 'https://www.baidu.com/s?wd=',
				name: '百度',
			}, {
				EngineValue: 'https://www.google.com/search?q=',
				name: '谷歌',
			},
			{
				EngineValue: 'https://cn.bing.com/search?q=',
				name: '必应',
			},
			{
				EngineValue: 'https://zh.wikipedia.org/wiki/',
				name: '维基百科',
			},
			{
				EngineValue: 'https://duckduckgo.com/?q=',
				name: 'DuckDuckGo',
			}
			],
			EngineValue: 'https://cn.bing.com/search?q=',
			SearchKey: '',
			BaiduData: '',
			BilibiliData: '',
			ToutiaoData: '',
			ZhihuData: '',
			WeatherData: '',
			hitokoto: ''
		};
	},
	computed: {
		formattedDate() {
			return new Date().toLocaleString();
		}
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
		}
	},
	mounted() {
		this.$http
			.get('https://ash345075666518.hostsh1.99web.top/hot/cors.php?file=baidu.json&' + new Date().getTime())
			.then(({ data }) => {
				this.BaiduData = data;
			});

		this.$http
			.get('https://ash345075666518.hostsh1.99web.top/hot/cors.php?file=bilibili.json&' + new Date().getTime())
			.then(({ data }) => {
				this.BilibiliData = data;
			});

		this.$http
			.get('https://ash345075666518.hostsh1.99web.top/hot/cors.php?file=toutiao.json&' + new Date().getTime())
			.then(({ data }) => {
				this.ToutiaoData = data;
			});

		this.$http
			.get('https://ash345075666518.hostsh1.99web.top/hot/cors.php?file=zhihu.json&' + new Date().getTime())
			.then(({ data }) => {
				this.ZhihuData = data;
			});

		this.$http
			.get('https://ash345075666518.hostsh1.99web.top/GetWeather.php')
			.then(({ data }) => {
				this.WeatherData = data.lives[0];
			});

		this.$http
			.get('https://v1.hitokoto.cn/')
			.then(({ data }) => {
				this.hitokoto = data.hitokoto;
			});
	}
};
</script>
<style>
.hot-container {
	margin-top: 80px;
}

.el-scrollbar__wrap {
	overflow-x: hidden;
}

html {
	overflow-x: hidden;
	background-image: url("https://blogimg.s3.ladydaily.com/icons/a1u3v-vjo0a.webp");
	background-size: cover;
}

a {
	text-decoration: none;
	color: black;
	display: block;
}

.weathers {
	top: 0;
	left: 0;
	width: 300px;
	height: 200px;
	display: inline-block;
	font-size: 15px;
	/* 设置字体大小为20px */
	line-height: 200px;
}

@font-face {
	font-family: Alibaba-PuHuiTi;
	font-style: normal;
	font-display: swap;
	src: url("https://blogimg.s3.ladydaily.com/Alibaba-PuHuiTi-Regular.subset.woff2") format("woff2");
}

* {
	font-family: Alibaba-PuHuiTi;
}

.container {
	margin-top: 10vh;
	display: flex;
	justify-content: center;
}

.select {
	margin-right: 5px;
}

.footer {
	justify-content: center;
	text-align: center;
}
</style>
<style scoped>
#form>>>.el-button {
	background: red;
}

.el-card ::v-deep .el-card__header {
	padding: 10px 10px;
	background-color: rgb(253, 242, 242);
	box-shadow: 0 2px 12px 0 rgba(31, 25, 25, 0.1);
}
</style>
  