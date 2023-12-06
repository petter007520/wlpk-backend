<template>
	<view class="main" id="bgMusic">
		<view class="icon" :class="{ ran: status }" @click="musicChange">
			<image :src="$util.img('upload/uniapp/audio-icon-new.png')" mode="widthFix" :style="icon_style"></image>
		</view>
		<view class="musicPlay" @touchstart.stop="playTo" v-if="musicPlay"></view>
	</view>
</template>

<script>
export default {
	props: {
		sizi: {
			default: '0.4',
			type: [Number, String]
		},
		autoplay: {
			default: true,
			type: Boolean
		},
		musicSrc: {
			default: '',
			type: String
		}
	},
	data() {
		return {
			icon_style: {},
			audioMusic: {},
			status: false,

			musicPlay: true
		};
	},
	beforeDestroy() {
		this.pauseTo();
	},
	watch: {
		'$route.path'(newVal, oldVal) {
			if (newVal != '/promotion/pintuanfission/detail') {
				this.pauseTo();
			}
		}
	},
	mounted() {
		this.icon_style = {
			width: this.sizi + 'rem'
			// height: this.sizi + 'rem'
		};
		this.$nextTick().then(res => {
			const audioMusic = uni.createInnerAudioContext();
			audioMusic.loop = true;
			audioMusic.onPlay(() => {
				this.status = true;
				this.musicPlay = false;
			});
			audioMusic.onPause(() => {
				this.status = false;
			});
			audioMusic.onError(err => {
				this.musicPlay = false;
				console.log(err);
			});

			audioMusic.src = this.musicSrc;
			if (this.autoplay) audioMusic.autoplay = true;
			this.audioMusic = audioMusic;
			if (!this.autoplay) return;
			setTimeout(() => {
				if (typeof WeixinJSBridge == 'undefined') {
					this.playTo();
				} else {
					WeixinJSBridge.invoke('getNetworkType', {}, e => {
						this.playTo();
					});
				}
			}, 500);
		});
	},
	methods: {
		playTo() {
			this.audioMusic.play();
		},
		pauseTo() {
			this.audioMusic.pause();
		},
		musicChange() {
			const status = this.status;
			if (status) {
				this.audioMusic.pause();
			} else {
				this.audioMusic.play();
			}
		}
	},
	destroyed() {}
};
</script>

<style lang="scss" scoped>
.icon {
	position: fixed;
	top: 0.6rem;
	right: 0.2rem;
	z-index: 999;
	image {
		width: 0.4rem;
		height: 0.4rem;
	}
}

.ran {
	image {
		animation: turn 1s linear infinite;
	}
}

@keyframes turn {
	0% {
		-webkit-transform: rotate(0deg);
	}

	25% {
		-webkit-transform: rotate(90deg);
	}

	50% {
		-webkit-transform: rotate(180deg);
	}

	75% {
		-webkit-transform: rotate(270deg);
	}

	100% {
		-webkit-transform: rotate(360deg);
	}
}

.musicPlay {
	position: fixed;
	top: 0;
	bottom: 0;
	right: 0;
	left: 0;
	background-color: rgba($color: #000000, $alpha: 0);
	z-index: 9999;
}
</style>
