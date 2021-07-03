(function () {
    function onClickLike(event) {
        event.preventDefault();
        const url=this.href;
        const nbDeLikes=document.querySelector('#js-hearts');
        const icone=document.querySelector('svg.fa-heart');
        // const icone_fa_prefix=icone.attr('data-prefix')
        axios.get(url).then(function(response) {
            nbDeLikes.textContent=response.data.likes;
            if (nbDeLikes.classList.contains('tomato') && icone.classList.contains('tomato')) {
                nbDeLikes.classList.replace('tomato', 'has-text-white');
                icone.classList.remove('tomato');
            }
            else {
                nbDeLikes.classList.replace('has-text-white', 'tomato');
                icone.classList.add('tomato');
            }
        }).catch(function(error) {
                if (error.response.status===403) {
                    alert('Vous ne pouvez pas aimé si vous n\'êtes pas connectés ...')
                }
                else {
                    alert('Une erreur s\'est produite ! Réessayer plus tard ...')
                }
            })
                
    }


document.querySelectorAll('a.js-heart').forEach(function(link) {
    link.addEventListener('click', onClickLike);
})
})()