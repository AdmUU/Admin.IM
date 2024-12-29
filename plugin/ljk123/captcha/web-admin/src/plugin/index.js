export default {
    install:(Vue)=>{
       const mains= import.meta.glob('./*/main.js')
       for (const path in mains) {
        mains[path]().then((module) => {
            Vue.use(module.default)
        });
      }
    }
}