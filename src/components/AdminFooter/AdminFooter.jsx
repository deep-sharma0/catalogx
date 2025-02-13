import './AdminFooter.scss';

const AdminFooter = () => {

    const supportLink = [
        {
          title: "Get in touch with Support",
          icon: "mail",
          description: "Reach out to the support team for assistance or guidance.",
          link: "https://catalogx.com/support/?utm_source=plugin&utm_medium=settings&utm_campaign=tracking",
        },
        {
          title: "Explore Documentation",
          icon: "submission-message",
          description: "Understand the plugin and its settings.",
          link: "https://catalogx.com/docs/?utm_source=plugin&utm_medium=settings&utm_campaign=tracking",
        },
        {
          title: "Contribute Here",
          icon: "support",
          description: "To participation in product enhancement.",
          link: "https://github.com/multivendorx/catalogx/issues?utm_source=plugin&utm_medium=settings&utm_campaign=tracking",
        },
      ];

    return (
        <>
            <div className="support-card">
            {supportLink.map((item, index) => {
                return (
                <>
                    <a href={item.link} target="_blank" className="card-item">
                    <i className={`admin-font adminLib-${item.icon}`}></i>
                    <a href={item.link} target="_blank">
                        {item.title}
                    </a>
                    <p>{item.description}</p>
                    </a>
                </>
                );
            })}
            </div>
        </>
    )
}
export default AdminFooter;
