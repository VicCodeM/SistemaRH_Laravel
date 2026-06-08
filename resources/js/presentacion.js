import Swiper from 'swiper';
import { Autoplay, Keyboard, Navigation, Pagination, Thumbs } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/thumbs';

window.Swiper = Swiper;
window.SwiperModules = {
    Autoplay,
    Keyboard,
    Navigation,
    Pagination,
    Thumbs,
};
