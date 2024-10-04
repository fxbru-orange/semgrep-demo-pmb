import Vue from "vue";
import messages from "../../common/helper/Messages.js";
import WebService from "../../common/helper/WebService.js";
import Notif from "../../common/helper/Notif";
import loader from "../../common/loader/loader.vue";
import formDiffusionsPrivate from "./components/formDiffusionsPrivate.vue";

Vue.prototype.messages = messages;
Vue.prototype.ws = new WebService($diffusionsPrivateData.webservice_url, false);
Vue.prototype.notif = Notif;

let loaderActive = false;
let loaderNeed = 0;
Vue.prototype.showLoader = () => {
	if (loaderActive) {
		loaderNeed++;
	} else {
		window.dispatchEvent(new Event("showLoader"));
		loaderActive = true;
	}
}

Vue.prototype.hiddenLoader = () => {
	if (loaderActive) {
		if (loaderNeed > 1) {
			loaderNeed--;
			return true;
		}
		setTimeout(() => {
			window.dispatchEvent(new Event("hiddenLoader"));
			loaderActive = false;
			loaderNeed = 0;
		}, 300);
	}
}
Vue.prototype.ws.addEventListener("fetch", () => {
	Vue.prototype.showLoader();
})
Vue.prototype.ws.addEventListener("response", () => {
	Vue.prototype.hiddenLoader();
})
Vue.prototype.ws.addEventListener("error", () => {
	Vue.prototype.hiddenLoader();
})

Vue.prototype.messages = messages;
new Vue({
	el:"#diffusionsPrivate",
	data: {
		pmb: pmbDojo.messages,
		formData : $diffusionsPrivateData.formData
	},
	components : {
        formDiffusionsPrivate,
		loader
	}
});