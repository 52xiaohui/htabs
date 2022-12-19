<template>
	<div id="app">
		<el-row type="flex" justify="center">
			<el-col :span="8">
				<el-button plain>
					{{WeatherData.city}}-{{WeatherData.weather}}-{{WeatherData.temperature}}℃
				</el-button>
			</el-col>
			<el-col :span="8" style="text-align: center">
				<el-tag type="success">
					<div style="font-size: 25px">{{date}}</div>
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
			<el-input v-model="SearchKey" @keyup.enter.native="go()" ref="Focusing" :placeholder=hitokoto
				style="width: 60vh">
				<i slot="suffix" class="el-input__icon el-icon-search" @click="go()"></i>
			</el-input>
		</div>

		<div style="margin-top: 15vh;"></div>
		<el-row :gutter="18">
			<el-col :md="6" :sm="12">
				<el-card style="box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1)">
					<div slot="header">
						<span>
							<img style="height: 15px"
								src="https://blogimg.s3.ladydaily.com/icons/78efd068-8302-435c-b086-fd920b928167.jpg" />
							百度热搜</span>
					</div>
					<div style="height: 49vh">
						<el-scrollbar style="height: 100%">
							<div v-for="(item,index) in BaiduData" :key="index">
								<a :href="item.url" target="_blank" rel="noopener noreferrer"
									style="display:inline;">{{index+1}}.
									{{item.title}}<span style="float: right; color: blue;">{{item.heat_score}}</span><i
										class="el-icon-hot-water" style="float: right;"></i></a>

								<el-divider></el-divider>
							</div>
						</el-scrollbar>
					</div>
				</el-card>
			</el-col>
			<el-col :md="6" :sm="12">
				<el-card>
					<div slot="header">
						<span>
							<img style="height: 15px"
								src="https://blogimg.s3.ladydaily.com/icons/743a0b0d-69bf-4cce-81c4-d256c8948e55.jpg" />
							今日头条</span>
					</div>
					<div style="height: 49vh">
						<el-scrollbar style="height: 100%">
							<div v-for="(item,index) in ToutiaoData" v-bind:key="index+1">
								<a :href="item.url" target="_blank" style="display: inline">{{index+1}}.
									{{item.title}}</a>

								<el-divider></el-divider>
							</div>
						</el-scrollbar>
					</div>
				</el-card>
			</el-col>
			<el-col :md="6" :sm="12">
				<el-card>
					<div slot="header">
						<span>
							<img style="height: 15px"
								src="https://blogimg.s3.ladydaily.com/icons/72204d0b-7233-4091-82e8-39285b0516ff.jpg" />
							聚合热榜</span>
					</div>
					<div style="height: 49vh">
						<el-scrollbar style="height: 100%">
							<div v-for="(item,index) in TophubData" v-bind:key="index+2">
								<a :href="item.url" target="_blank" rel="noopener noreferrer"
									style="display:inline;">{{index+1}}. {{item.title}}
									<span style="float: right; color: blue;">{{item.sitename}}</span><i
										class="el-icon-ice-cream-round" style="float: right;"></i></a>

								<el-divider></el-divider>
							</div>
						</el-scrollbar>
					</div>
				</el-card>
			</el-col>
			<el-col :md="6" :sm="12">
				<el-card>
					<div slot="header">
						<span>
							<img style="height: 15px"
								src="https://blogimg.s3.ladydaily.com/icons/2f270abe-01f8-4d8c-8df9-5ef8dd323c9c.jpg" />
							知乎热搜</span>
					</div>
					<div style="height: 49vh">
						<el-scrollbar style="height: 100%">
							<div v-for="(item,index) in ZhihuData" v-bind:key="index+3">
								<a :href="item.url" target="_blank" style="display: inline">{{index+1}}.
									{{item.title}}</a>

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
					}
				],
				EngineValue: 'https://www.google.com/search?q=',
				SearchKey: '',
				BaiduData: '',
				ZhihuData: '',
				ToutiaoData: '',
				TophubData: '',
				date: new Date(),
				WeatherData: '',
				hitokoto: '',
			}
		},
		created() {
			if (localStorage.getItem("SearchUrl") != null) {
				this.EngineValue = window.localStorage.getItem('SearchUrl');
			}

		},
		methods: {
			currStationChange(val) {
				window.localStorage.setItem('SearchUrl', val)
				console.log(val)
			},
			go() {
				var substance = this.EngineValue + this.SearchKey;
				window.open(substance);
			},
		},

		mounted() {
			// this.$refs.Focusing.focus();
			this.$http.get('https://ash623941490038.hostsh1.99web.top/data/baidu.php?' + new Date().getTime())
				.then((GetBaiduData) => {
					this.BaiduData = GetBaiduData.data.data
				})
			this.$http.get('https://ash623941490038.hostsh1.99web.top/data/zhihu.php?' + new Date().getTime())
				.then((GetZhihuData) => {
					this.ZhihuData = GetZhihuData.data.data
				})
			this.$http.get('https://ash623941490038.hostsh1.99web.top/data/toutiao.php?' + new Date().getTime())
				.then((GetToutiaoData) => {
					this.ToutiaoData = GetToutiaoData.data.data
				})
			this.$http.get('https://ash623941490038.hostsh1.99web.top/data/tophub.php?' + new Date().getTime())
				.then((GetTophubData) => {
					this.TophubData = GetTophubData.data.data.items
				})
			this.$http.get('https://ash623941490038.hostsh1.99web.top/weather/')
				.then((GetWeatherData) => {
					this.WeatherData = GetWeatherData.data.lives[0]
				})
			this.$http.get('https://v1.hitokoto.cn/')
				.then((Gethitokoto) => {
					this.hitokoto = Gethitokoto.data.hitokoto
				})
			this.timer = setInterval(() => {
				this.date = new Date().toLocaleString();
			});
		},
		beforeDestroy: function() {
			if (this.timer) {
				clearInterval(this.timer);
			}
		},
	}
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
