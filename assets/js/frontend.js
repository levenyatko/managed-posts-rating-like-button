const buttons = Object.values( document.getElementsByClassName("mpr-button__active") );
const xhr = new XMLHttpRequest();

if ( 0 < buttons.length ) {
   buttons.forEach(button => {

      button.addEventListener("click", function () {

         xhr.open("POST", mpr_vars.apibase + '/v1/rate', true);
         xhr.responseType = 'json';
         xhr.setRequestHeader('X-WP-Nonce', mpr_vars.nonce);

         xhr.onreadystatechange = () => {
            button.text = xhr.status;
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {

               const tooltip = this.querySelector('.mpr-tooltip');
               tooltip.querySelector('p').innerHTML = xhr.response.message;

               if (xhr.response.success) {
                  tooltip.classList.add('mpr-success');
                  this.querySelector('.mpr-votes-number').innerHTML = xhr.response.new_rating;
                  this.classList.add('mpr-voted');
               } else {
                  tooltip.classList.add('mpr-error');
               }
               tooltip.classList.add('mpr-display');

               setTimeout(function () {
                  tooltip.classList.remove('mpr-display');
                  tooltip.classList.remove('mpr-error');
                  tooltip.classList.remove('mpr-success');
               }, 1500);

            }
         }

         let data = new FormData();
         data.append('id', this.dataset.post);
         data.append('parent_id', this.dataset.parent);

         xhr.send(data);

      });

   });
}
