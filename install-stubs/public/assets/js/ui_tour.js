$(function() {
  function setupTour(tour) {
    var backButtonClass = 'btn btn-sm btn-secondary md-btn-flat';
    var nextButtonClass = 'btn btn-sm btn-primary';
    var isRtl = $('html').attr('dir') === 'rtl';

    tour.addStep('tour-1', {
      title: 'Title of first step',
      text: ['Content of first step', '<strong>Second</strong> line'],
      attachTo: '#tour-1 ' + (isRtl ? 'left' : 'right'),
      buttons: [{
        action: tour.cancel,
        classes: backButtonClass,
        text: 'Exit'
      }, {
        action: tour.next,
        classes: nextButtonClass,
        text: 'Next'
      }]
    });
    tour.addStep('tour-2', {
      title: 'Title of second step',
      text: 'Content of second step',
      attachTo: '#tour-2 ' + (isRtl ? 'right' : 'left'),
      buttons: [{
        action: tour.back,
        classes: backButtonClass,
        text: 'Back'
      }, {
        action: tour.next,
        classes: nextButtonClass,
        text: 'Next'
      }]
    });
    tour.addStep('tour-3', {
      title: 'Title of third step',
      text: 'Content of third step',
      attachTo: '#tour-3 bottom',
      buttons: [{
        action: tour.back,
        classes: backButtonClass,
        text: 'Back'
      }, {
        action: tour.next,
        classes: nextButtonClass,
        text: 'Next'
      }]
    });
    tour.addStep('tour-4', {
      title: 'Title of fourth step',
      text: 'Content of fourth step',
      attachTo: '#tour-4 top',
      buttons: [{
        action: tour.back,
        classes: backButtonClass,
        text: 'Back'
      }, {
        action: tour.next,
        classes: nextButtonClass,
        text: 'Next'
      }]
    });
    tour.addStep('tour-modal', {
      title: 'Floating modal',
      text: 'Content of floating modal step',
      buttons: [{
        action: tour.back,
        classes: backButtonClass,
        text: 'Back'
      }, {
        action: tour.next,
        classes: nextButtonClass,
        text: 'Next'
      }]
    });
    tour.addStep('tour-5', {
      title: 'Title of fifth step',
      text: 'Content of fifth step',
      attachTo: '#tour-5 bottom',
      buttons: [{
        action: tour.back,
        classes: backButtonClass,
        text: 'Back'
      }, {
        action: tour.next,
        classes: nextButtonClass,
        text: 'Done'
      }]
    });

    return tour;
  }

  $('#shepherd-example').click(function () {
    var tour = new Shepherd.Tour({
      defaultStepOptions: {
        scrollTo: false,
        showCancelLink: true
      },
      useModalOverlay: true
    });

    setupTour(tour).start();
  });
});
