<?php
/**
 * Video Carousel Template Part
 * Renders the fullscreen video carousel using Swiper and meta box data.
 */

$slides = get_post_meta( get_the_ID(), '_basecamp_video_carousel_slides', true );
if ( ! is_array( $slides ) || empty( $slides ) ) {
	return;
}

// Enqueue Swiper assets (ensure these are enqueued only once in your theme)
wp_enqueue_style( 'swiper', get_site_url() . '/assets/css/resources/swiper.min.css', [], null );
wp_enqueue_script( 'swiper', get_site_url() . '/assets/js/resources/swiper.min.js', [], null, true );
?>

<div class="video-carousel-container">
	<div class="swiper video-carousel-swiper">
		<div class="swiper-wrapper">
			<?php foreach ( $slides as $i => $slide ) : ?>
				<div class="swiper-slide">
					<div class="video-carousel-slide">
						<video
							class="video-carousel-video"
							playsinline
							preload="none"
							data-desktop-src="<?php echo esc_url( $slide['desktop_video'] ?? '' ); ?>"
							data-mobile-src="<?php echo esc_url( $slide['mobile_video'] ?? '' ); ?>"
							data-desktop-poster="<?php echo esc_url( $slide['poster'] ?? '' ); ?>"
							data-mobile-poster="<?php echo esc_url( $slide['mobile_poster'] ?? '' ); ?>"
							controls
							tabindex="0"
							loop
						></video>
						<?php if ( ! empty( $slide['audio'] ) ) : ?>
							<audio class="video-carousel-audio" preload="none" src="<?php echo esc_url( $slide['audio'] ); ?>"></audio>
						<?php endif; ?>
						<?php if ( ! empty( $slide['overlay'] ) ) : ?>
							<div class="video-carousel-overlay">
								<?php echo wp_kses_post( nl2br( $slide['overlay'] ) ); ?>
							</div>
						<?php endif; ?>
						<div class="video-carousel-controls">
							<button class="video-play-pause" aria-label="Play/Pause"></button>
							<?php if ( ! empty( $slide['audio'] ) ) : ?>
								<button class="audio-play-pause" aria-label="Audio Play/Pause"></button>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="swiper-button-prev" tabindex="0" role="button" aria-label="Previous slide"></div>
		<div class="swiper-button-next" tabindex="0" role="button" aria-label="Next slide"></div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const isMobile = window.matchMedia('(max-width: 767px)').matches;
	const videos = document.querySelectorAll('.video-carousel-video');
	const audios = document.querySelectorAll('.video-carousel-audio');
	const playPauseBtns = document.querySelectorAll('.video-play-pause');
	const audioBtns = document.querySelectorAll('.audio-play-pause');
	let userInitiatedPlayback = false;

	videos.forEach(function(video) {
		const src = isMobile ? video.dataset.mobileSrc : video.dataset.desktopSrc;
		if (src) video.src = src;
	});

	function setActivePoster(idx) {
		videos.forEach(function(video, i) {
			if (i === idx) {
				const poster = isMobile ? video.dataset.mobilePoster : video.dataset.desktopPoster;
				if (poster) video.setAttribute('poster', poster);
			} else {
				video.removeAttribute('poster');
			}
		});
	}

	function preloadPoster(idx) {
		if (videos[idx]) {
			const poster = isMobile ? videos[idx].dataset.mobilePoster : videos[idx].dataset.desktopPoster;
			if (poster) {
				const img = new Image();
				img.src = poster;
			}
		}
	}

	// Preload next/prev poster images on slide change
	function preloadAdjacentPosters() {
		const nextIdx = swiper.activeIndex + 1;
		const prevIdx = swiper.activeIndex - 1;
		preloadPoster(nextIdx);
		preloadPoster(prevIdx);
	}

	// Set poster for the first (active) slide only
	setActivePoster(0);

	const swiper = new Swiper('.video-carousel-swiper', {
		effect: 'fade',
		fadeEffect: { crossFade: true },
		navigation: {
			nextEl: '.swiper-button-next',
			prevEl: '.swiper-button-prev',
		},
		lazy: false,
		loop: false,
		on: {
			slideChange: function() {
				videos.forEach(function(video, idx) {
					if (idx === swiper.activeIndex) {
						video.load();
						if (userInitiatedPlayback) {
							video.play();
							playPauseBtns[idx]?.classList.add('playing');
						}
					} else {
						video.pause();
						video.currentTime = 0;
						playPauseBtns[idx]?.classList.remove('playing');
					}
				});
				audios.forEach(function(audio, idx) {
					if (idx !== swiper.activeIndex) {
						audio.pause();
						audio.currentTime = 0;
						audioBtns[idx]?.classList.remove('playing');
					}
					preloadAdjacentPosters();
					setActivePoster(swiper.activeIndex);
				});
			}
		}
	});

	// Initial preload for first slide's neighbors
	preloadAdjacentPosters();

	// Play/Pause video button (always controls active slide)
	playPauseBtns.forEach(function(btn, idx) {
		btn.addEventListener('click', function() {
			const activeIdx = swiper.activeIndex;
			const video = videos[activeIdx];
			if (video.paused) {
				video.play();
				btn.classList.add('playing');
				userInitiatedPlayback = true;
			} else {
				video.pause();
				btn.classList.remove('playing');
			}
		});
	});

	// Play/Pause audio button (always controls active slide)
	audioBtns.forEach(function(btn, idx) {
		btn.addEventListener('click', function() {
			const activeIdx = swiper.activeIndex;
			const audio = audios[activeIdx];
			if (audio.paused) {
				audio.play();
				btn.classList.add('playing');
			} else {
				audio.pause();
				btn.classList.remove('playing');
			}
		});
	});

	// Set userInitiatedPlayback to true when navigation is used
	document.querySelector('.swiper-button-next').addEventListener('click', function() {
		if (userInitiatedPlayback) {
			// Already set, do nothing
		} else {
			// If a video is currently playing, set flag
			const activeIdx = swiper.activeIndex;
			if (!videos[activeIdx].paused) {
				userInitiatedPlayback = true;
			}
		}
	});
	document.querySelector('.swiper-button-prev').addEventListener('click', function() {
		if (userInitiatedPlayback) {
			// Already set, do nothing
		} else {
			const activeIdx = swiper.activeIndex;
			if (!videos[activeIdx].paused) {
				userInitiatedPlayback = true;
			}
		}
	});
});
</script>
